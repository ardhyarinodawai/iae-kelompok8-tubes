export function authHeaders(token: string) {
  return token
    ? {
        Authorization: `Bearer ${token}`,
      }
    : undefined;
}

export async function jsonFetch<T>(
  url: string,
  init?: RequestInit,
): Promise<T> {
  const res = await fetch(url, {
    ...init,
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json",
      ...(init?.headers || {}),
    },
  });

  const text = await res.text();

  const data = text
    ? (() => {
        try {
          return JSON.parse(text);
        } catch {
          return text;
        }
      })()
    : null;

  if (!res.ok) {
    const msg =
      data &&
      typeof data === "object" &&
      "message" in (data as Record<string, unknown>)
        ? String((data as { message: unknown }).message)
        : `${res.status} ${res.statusText}`;

    throw new Error(msg);
  }

  return data as T;
}

export function unwrap<T>(payload: unknown): T[] {
  if (Array.isArray(payload)) return payload;

  if (payload && typeof payload === "object") {
    const obj = payload as Record<string, unknown>;

    if (Array.isArray(obj.data)) {
      return obj.data as T[];
    }

    if (
      obj.data &&
      typeof obj.data === "object" &&
      Array.isArray((obj.data as Record<string, unknown>).data)
    ) {
      return (obj.data as { data: T[] }).data;
    }
  }

  return [];
}