import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";

import type { ServiceUrls } from "@/types";

type Props = {
  urls: ServiceUrls;
  saveUrls: (urls: ServiceUrls) => void;
};

export default function ApiConfigForm({ urls, saveUrls }: Props) {
  const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();

    const fd = new FormData(e.currentTarget);

    saveUrls({
      tickets: String(fd.get("tickets") || "").trim() || urls.tickets,

      listings: String(fd.get("listings") || "").trim() || urls.listings,

      contracts: String(fd.get("contracts") || "").trim() || urls.contracts,

      ticketsToken: String(fd.get("ticketsToken") || "").trim(),
    });
  };

  return (
    <form className="grid gap-4" onSubmit={handleSubmit}>
      <div className="grid gap-2">
        <Label htmlFor="u-tickets">Service Manajemen Tiket (Dawai)</Label>

        <Input
          id="u-tickets"
          name="tickets"
          defaultValue={urls.tickets}
          placeholder={urls.tickets}
        />
      </div>

      <div className="grid gap-2">
        <Label htmlFor="u-tickets-token">Bearer Token (Service Tiket)</Label>

        <Input
          id="u-tickets-token"
          name="ticketsToken"
          type="password"
          defaultValue={urls.ticketsToken}
          placeholder={
            urls.ticketsToken
              ? "Token tersimpan (hidden)"
              : "Masukkan token bearer..."
          }
        />
      </div>

      <div className="grid gap-2">
        <Label htmlFor="u-listings">Service Listing Unit (Rafsan)</Label>

        <Input
          id="u-listings"
          name="listings"
          defaultValue={urls.listings}
          placeholder={urls.listings}
        />
      </div>

      <div className="grid gap-2">
        <Label htmlFor="u-contracts">Service Kontrak Sewa (Akhdan)</Label>

        <Input
          id="u-contracts"
          name="contracts"
          defaultValue={urls.contracts}
          placeholder={urls.contracts}
        />
      </div>

      <div className="flex justify-end">
        <Button type="submit">Simpan Konfigurasi</Button>
      </div>
    </form>
  );
}
