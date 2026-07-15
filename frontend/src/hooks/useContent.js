import { useEffect, useState } from "react";
import { api } from "../api";

export function useContent(resource) {
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(true);
  useEffect(() => {
    let active = true;
    const load = () => { setLoading(true); api(`/content/${resource}`).then(value => active && setData(value)).finally(() => active && setLoading(false)); };
    load();
    window.addEventListener("icm:content-updated", load);
    return () => { active = false; window.removeEventListener("icm:content-updated", load); };
  }, [resource]);
  return [data, loading];
}

export function useSettings() {
  const [settings, setSettings] = useState({});
  useEffect(() => {
    let active = true;
    const load = () => api("/content/settings").then(value => active && setSettings(value));
    load();
    window.addEventListener("icm:content-updated", load);
    return () => { active = false; window.removeEventListener("icm:content-updated", load); };
  }, []);
  return settings;
}
