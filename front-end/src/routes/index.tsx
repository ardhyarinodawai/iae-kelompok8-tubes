import { createFileRoute } from "@tanstack/react-router";
import { useCallback, useEffect, useState } from "react";
import { toast, Toaster } from "sonner";

import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";

import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";

import TicketForm from "../components/ui/TicketForm";
import TicketHistory from "../components/ui/TicketHistory";
import ApiConfigForm from "../components/ui/ApiConfigForm";

import { ApiService } from "@/services/api";
import { useServiceUrls } from "@/hooks/useServiceUrls";

import { unwrap } from "@/utils/helpers";

import type { Ticket, Listing, Contract, Tenant } from "@/types";

export const Route = createFileRoute("/")({
  head: () => ({
    meta: [
      {
        title: "Pengajuan Keluhan Tiket Tenant",
      },
      {
        name: "description",
        content:
          "Form pengajuan tiket keluhan tenant terhadap unit properti yang disewa.",
      },
      {
        property: "og:title",
        content: "Pengajuan Keluhan Tiket Tenant",
      },
      {
        property: "og:description",
        content:
          "Form pengajuan tiket keluhan tenant terhadap unit properti yang disewa.",
      },
    ],
  }),
  component: Index,
});

function Index() {
  const { urls, saveUrls } = useServiceUrls();

  const [tickets, setTickets] = useState<Ticket[]>([]);
  const [listings, setListings] = useState<Listing[]>([]);
  const [contracts, setContracts] = useState<Contract[]>([]);
  const [tenants, setTenants] = useState<Tenant[]>([]);

  const [loading, setLoading] = useState(false);

  const refreshData = useCallback(async () => {
    try {
      setLoading(true);

      // Panggil ketiga API secara bersamaan
      const [ticketsRes, listingsRes, contractsRes, tenantsRes] =
        await Promise.all([
          ApiService.getTickets(urls.tickets, urls.ticketsToken),
          ApiService.getListings(urls.listings),
          ApiService.getContracts(urls.contracts),
          ApiService.getTenants(urls.contracts),
        ]);

      // Simpan datanya ke state masing-masing
      setTickets(unwrap<Ticket>(ticketsRes));
      setListings(unwrap<Listing>(listingsRes));
      setContracts(unwrap<Contract>(contractsRes));
      setTenants(unwrap<Tenant>(tenantsRes));

      toast.success("Berhasil mengambil data service");
    } catch (error) {
      console.error(error);

      toast.error("Gagal mengambil data service");

      setTickets([]);
      setListings([]);
      setContracts([]);
    } finally {
      setLoading(false);
    }
  }, [urls]);

  useEffect(() => {
    void refreshData();
  }, [refreshData]);

  const refreshTickets = useCallback(async () => {
    try {
      setLoading(true);

      const [ticketsResponse] = await Promise.all([
        ApiService.getTickets(urls.tickets, urls.ticketsToken),
      ]);

      setTickets(unwrap<Ticket>(ticketsResponse));

      toast.success("Berhasil mengambil data service");
    } catch (error) {
      console.error(error);

      toast.error("Gagal mengambil data service");

      setTickets([]);
    } finally {
      setLoading(false);
    }
  }, [urls]);

  return (
    <div className="min-h-screen bg-background">
      <Toaster richColors position="top-right" />

      <header className="border-b bg-card">
        <div className="mx-auto max-w-5xl px-6 py-6">
          <h1 className="text-2xl font-semibold tracking-tight">
            Pengajuan Keluhan Tiket Tenant
          </h1>

          <p className="mt-2 text-sm text-muted-foreground">
            Integrasi Service Listing, Contract, dan Ticket Management.
          </p>
        </div>
      </header>

      <main className="mx-auto max-w-5xl px-6 py-8">
        <Tabs defaultValue="submit" className="w-full">
          <TabsList>
            <TabsTrigger value="submit">Ajukan Tiket</TabsTrigger>

            <TabsTrigger value="history">Riwayat Tiket</TabsTrigger>

            <TabsTrigger value="config">Konfigurasi API</TabsTrigger>
          </TabsList>

          <TabsContent value="submit" className="mt-6">
            <Card>
              <CardHeader>
                <CardTitle>Form Keluhan Tenant</CardTitle>

                <CardDescription>
                  Buat tiket baru untuk melaporkan kerusakan atau keluhan unit
                  properti.
                </CardDescription>
              </CardHeader>

              <CardContent>
                <TicketForm
                  listings={listings}
                  tenants={tenants}
                  ticketUrl={urls.tickets}
                  token={urls.ticketsToken}
                  onSuccess={refreshData}
                />
              </CardContent>
            </Card>
          </TabsContent>

          <TabsContent value="history" className="mt-6">
            <Card>
              <CardHeader>
                <CardTitle>Riwayat Tiket</CardTitle>

                <CardDescription>
                  Daftar tiket yang tersimpan pada service ticket management.
                </CardDescription>
              </CardHeader>

              <CardContent>
                <TicketHistory
                  tickets={tickets}
                  loading={loading}
                  onRefresh={refreshTickets}
                />
              </CardContent>
            </Card>
          </TabsContent>

          <TabsContent value="config" className="mt-6">
            <Card>
              <CardHeader>
                <CardTitle>Konfigurasi API</CardTitle>

                <CardDescription>
                  Atur URL service yang digunakan aplikasi.
                </CardDescription>
              </CardHeader>

              <CardContent>
                <ApiConfigForm urls={urls} saveUrls={saveUrls} />
              </CardContent>
            </Card>
          </TabsContent>
        </Tabs>
      </main>
    </div>
  );
}
