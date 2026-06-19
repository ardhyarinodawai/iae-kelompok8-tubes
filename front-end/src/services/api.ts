import { authHeaders, jsonFetch } from "@/utils/helpers";

export const ApiService = {
  getListings(baseUrl: string) {
    return jsonFetch(`${baseUrl}/listings`);
  },

  getContracts(baseUrl: string) {
    return jsonFetch(`${baseUrl}/contracts`, {
      headers: {
        "X-API-KEY": "102022400056"
      }
    });
  },

  getTenants(baseUrl: string) {
    return jsonFetch(`${baseUrl}/tenants`, {
      headers: { "X-API-KEY": "102022400056" }
    });
  },

  getTickets(baseUrl: string, token: string) {
    return jsonFetch(`${baseUrl}/tickets`, {
      headers: authHeaders(token),
    });
  },

  createTicket(
    baseUrl: string,
    token: string,
    payload: unknown,
  ) {
    return jsonFetch(`${baseUrl}/tickets`, {
      method: "POST",
      headers: authHeaders(token),
      body: JSON.stringify(payload),
    });
  },
};