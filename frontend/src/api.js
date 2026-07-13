const API_URL = import.meta.env.VITE_API_URL || "/api";
const responseCache = new Map();
const pendingRequests = new Map();
const CACHE_TTL = 60_000;

export async function api(path, options = {}) {
  const method = (options.method || "GET").toUpperCase();
  const token = localStorage.getItem("icm_admin_token");
  const isPublicGet = method === "GET" && !token && path.startsWith("/content/");
  const cached = responseCache.get(path);
  if (isPublicGet && cached && Date.now() - cached.time < CACHE_TTL) return cached.data;
  if (isPublicGet && pendingRequests.has(path)) return pendingRequests.get(path);

  const isFormData = options.body instanceof FormData;
  const request = fetch(`${API_URL}${path}`, {
    ...options,
    headers: { ...(!isFormData ? { "Content-Type": "application/json" } : {}), Accept: "application/json", ...(token ? { Authorization: `Bearer ${token}` } : {}), ...options.headers },
  }).then(async response => {
    if (response.status===401&&token){localStorage.removeItem("icm_admin_token");sessionStorage.clear()}
    if (!response.ok) { const body = await response.json().catch(() => ({})); const details=body.errors?Object.values(body.errors).flat().join(" "):"";throw new Error(details||body.message||"Request failed"); }
    const data = response.status === 204 ? null : await response.json();
    if (isPublicGet) responseCache.set(path, { data, time: Date.now() });
    if (method !== "GET") responseCache.clear();
    return data;
  }).finally(() => pendingRequests.delete(path));

  if (isPublicGet) pendingRequests.set(path, request);
  return request;
}
