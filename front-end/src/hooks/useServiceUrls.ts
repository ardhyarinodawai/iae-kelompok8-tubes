import { useState } from "react";
import { toast } from "sonner";
import type { ServiceUrls } from "@/types";

export const LS_KEY = "service_urls_v1";

export const DEFAULTS: ServiceUrls = {
  tickets: "http://localhost:8080/api/v1/ticket-service",
  ticketsToken: "",
  listings: "http://localhost:8080/api/v1/listing-service",
  contracts: "http://localhost:8080/api/v1/contract-service",
};

export function useServiceUrls() {
  const [urls, setUrls] = useState(() => {
    try {
      const raw = localStorage.getItem(LS_KEY);

      if (!raw) return DEFAULTS;

      return {
        ...DEFAULTS,
        ...JSON.parse(raw),
      };
    } catch {
      return DEFAULTS;
    }
  });

  const saveUrls = (next: ServiceUrls) => {
    setUrls(next);

    localStorage.setItem(
      LS_KEY,
      JSON.stringify(next),
    );

    toast.success("URL service tersimpan");
  };

  return {
    urls,
    saveUrls,
  };
}