import { defineConfig, loadEnv } from "vite";
import react from "@vitejs/plugin-react";
import { writeFileSync } from "node:fs";

const routes=["/","/mall","/directory","/leasing","/events","/about","/services","/inquire","/privacy-policy","/terms-of-service","/cookies-policy"];
function sitemapPlugin(siteUrl){return{name:"icm-sitemap",closeBundle(){const base=siteUrl.replace(/\/$/,"");const xml=`<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n${routes.map(route=>`  <url><loc>${base}${route}</loc></url>`).join("\n")}\n</urlset>\n`;writeFileSync("dist/sitemap.xml",xml);writeFileSync("dist/robots.txt",`User-agent: *\nAllow: /\nDisallow: /admin\nSitemap: ${base}/sitemap.xml\n`)}}}

export default defineConfig(({mode})=>{const env=loadEnv(mode,process.cwd(),"");return {
  plugins: [react(),sitemapPlugin(env.VITE_SITE_URL||"http://localhost:5173")],
  server: {
    port: 5173,
    proxy: { "/api": "http://127.0.0.1:8000" },
  },
}});
