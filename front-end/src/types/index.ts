export type Listing = {
  id: number | string;
  name?: string;
  address?: string;
  [k: string]: unknown;
};

export interface Tenant {
  id: number;
  name: string;
  email: string;
}

export type Contract = {
  id: number | string;
  tenant_name?: string;
  listing_id?: number | string;
  status?: string;
  tenant?: Tenant;
  [k: string]: unknown;
};

export type Ticket = {
  id: number | string;
  listing_id?: number | string;
  contract_id?: number | string;
  description?: string;
  soap_receipt?: string;
  [k: string]: unknown;
};

export type ServiceUrls = {
  tickets: string;
  ticketsToken: string;
  listings: string;
  contracts: string;
};