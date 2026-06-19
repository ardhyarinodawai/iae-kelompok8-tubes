import { useState } from "react";
import { toast } from "sonner";
import { Button } from "@/components/ui/button";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { ApiService } from "@/services/api";

// Hapus Contract dari import jika sudah tidak dipakai di file ini
import type { Listing, Tenant } from "@/types";

type Props = {
  ticketUrl: string;
  token: string;
  listings?: Listing[];
  tenants?: Tenant[];
  onSuccess: () => Promise<void>;
};

export default function TicketForm({
  ticketUrl,
  token,
  listings = [],
  tenants = [],
  onSuccess,
}: Props) {
  const [listingId, setListingId] = useState("");
  const [tenantId, setTenantId] = useState("");
  const [description, setDescription] = useState("");

  const [submitting, setSubmitting] = useState(false);

  const submitTicket = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();

    if (!listingId || !tenantId || !description.trim()) {
      toast.error("Mohon lengkapi semua field");
      return;
    }

    const selectedTenant = tenants.find((t) => String(t.id) === tenantId);

    try {
      setSubmitting(true);

      // Payload sekarang langsung mengirimkan tenant_id, biarkan backend yang mencari kontraknya
      await ApiService.createTicket(ticketUrl, token, {
        listing_id: Number(listingId),
        tenant_id: Number(tenantId),
        tenant_name: selectedTenant?.name || "Unknown",
        tenant_email: selectedTenant?.email || "unknown@example.com",
        description: description.trim(),
      });

      toast.success("Tiket keluhan berhasil dikirim");

      setListingId("");
      setTenantId("");
      setDescription("");

      await onSuccess();
    } catch (error) {
      // Jika backend melempar error validasi (misal: kontrak tidak ditemukan), akan ditangkap di sini
      toast.error(
        error instanceof Error ? error.message : "Gagal mengirim tiket",
      );
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <form onSubmit={submitTicket} className="grid gap-5">
      <div className="grid gap-4 md:grid-cols-2">
        {/* DROPDOWN UNIT PROPERTI */}
        <div className="grid gap-2">
          <Label>Unit Properti</Label>
          <Select value={listingId} onValueChange={setListingId}>
            <SelectTrigger>
              <SelectValue placeholder="Pilih Unit Properti" />
            </SelectTrigger>
            <SelectContent>
              {listings.map((listing, index) => {
                const safeId = listing.id
                  ? String(listing.id)
                  : `listing-fallback-${index}`;

                return (
                  <SelectItem key={safeId} value={safeId}>
                    {listing.name ?? `Listing #${listing.id || "Unknown"}`}
                  </SelectItem>
                );
              })}
            </SelectContent>
          </Select>
        </div>

        {/* DROPDOWN TENANT */}
        <div className="grid gap-2">
          <Label>Nama Tenant</Label>
          <Select value={tenantId} onValueChange={setTenantId}>
            <SelectTrigger>
              <SelectValue placeholder="Pilih Nama Tenant" />
            </SelectTrigger>
            <SelectContent>
              {tenants.map((tenant, index) => {
                const safeId = tenant.id
                  ? String(tenant.id)
                  : `tenant-fallback-${index}`;

                return (
                  <SelectItem key={safeId} value={safeId}>
                    {tenant.name || "Unknown Tenant"}
                  </SelectItem>
                );
              })}
            </SelectContent>
          </Select>
        </div>
      </div>

      <div className="grid gap-2">
        <Label htmlFor="description">Detail Kerusakan</Label>
        <Textarea
          id="description"
          value={description}
          onChange={(e) => setDescription(e.target.value)}
          placeholder="Jelaskan kerusakan secara detail..."
          rows={5}
          maxLength={1000}
        />
      </div>

      <div className="flex items-center justify-between">
        <p className="text-xs text-muted-foreground">
          Backend akan melakukan pencarian kontrak dan validasi.
        </p>
        <Button type="submit" disabled={submitting}>
          {submitting ? "Mengirim..." : "Kirim Tiket"}
        </Button>
      </div>
    </form>
  );
}
