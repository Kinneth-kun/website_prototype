import { useEffect, useState } from "react";
import { api } from "../api";

export function useContent(resource) {
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(true);
  useEffect(() => {
    let active = true;
    setLoading(true);
    api(`/content/${resource}`).then(value => active && setData(value)).finally(() => active && setLoading(false));
    return () => { active = false; };
  }, [resource]);
  return [data, loading];
}

export function useSettings() {
  const [settings, setSettings] = useState({});
  useEffect(() => { api("/content/settings").then(setSettings); }, []);
  return settings;
}
