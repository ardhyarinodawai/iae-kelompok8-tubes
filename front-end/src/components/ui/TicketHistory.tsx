import { Button } from "@/components/ui/button";

import type { Ticket } from "@/types";

type Props = {
  tickets: Ticket[];
  loading: boolean;
  onRefresh: () => Promise<void>;
};

export default function TicketHistory({ tickets, loading, onRefresh }: Props) {
  return (
    <div className="space-y-4">
      <div className="flex justify-end">
        <Button
          variant="outline"
          size="sm"
          onClick={() => void onRefresh()}
          disabled={loading}
        >
          {loading ? "Memuat..." : "Refresh"}
        </Button>
      </div>

      {tickets.length === 0 ? (
        <div className="rounded-lg border p-8 text-center">
          <p className="text-sm text-muted-foreground">
            Belum ada tiket yang tersedia.
          </p>
        </div>
      ) : (
        <div className="grid gap-3">
          {tickets.map((ticket) => (
            <div
              key={String(ticket.id)}
              className="rounded-lg border bg-card p-4"
            >
              <div className="flex items-start justify-between gap-3">
                <div className="min-w-0 flex-1">
                  <h3 className="font-medium">Ticket ID #{ticket.id}</h3>

                  {ticket.description && (
                    <p className="mt-2 text-sm text-muted-foreground">
                      {ticket.description}
                    </p>
                  )}

                  <div className="mt-3 flex flex-wrap gap-4 text-xs text-muted-foreground">
                    {ticket.listing_id !== undefined && (
                      <span>Unit ID: #{String(ticket.listing_id)}</span>
                    )}

                    {ticket.contract_id !== undefined && (
                      <span>Contract ID: #{String(ticket.contract_id)}</span>
                    )}
                  </div>

                  {ticket.soap_receipt && (
                    <div className="mt-2">
                      <span className="text-xs text-muted-foreground">
                        SOAP Receipt:
                      </span>

                      <p className="mt-1 rounded bg-muted p-2 text-xs break-all">
                        {ticket.soap_receipt}
                      </p>
                    </div>
                  )}
                </div>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
