import { Building2, CalendarDays, Send, Store, Tags, Wrench } from "lucide-react";

export function assetUrl(path) {
  if (!path) return "";
  return /^(https?:)?\/\//.test(path) ? path : `/${String(path).replace(/^\//, "")}`;
}

export const publicNavigation = [
  ["/", "Home"], ["/mall", "Mall"],
  ["/leasing", "Leasing"], ["/about", "About Us"],
  ["/services", "Services"], ["/inquire", "Inquire"],
];

export const mallNavigation = [
  ["/directory", "Directory"],
  ["/events", "Events & Promos"],
];

export const adminResources = [
  ["tenants", "Tenants", Store], ["categories", "Categories", Tags],
  ["floors", "Floors", Building2], ["leasing_spaces", "Leasing spaces", Building2],
  ["events", "Events", CalendarDays], ["promotions", "Promotions", Tags],
  ["services", "Services", Wrench], ["highlights", "Media highlights", CalendarDays],
  ["inquiries", "Inquiries", Send], ["mall_hours", "Mall hours", CalendarDays],
  ["site_settings", "Settings", Wrench],
  ["media", "Media library", CalendarDays],
];

export const adminFields = {
  tenants: ["name","category_id","floor_id","location_detail","lease_type","logo_url","description","status","is_featured","display_order","published_at"],
  categories: ["name","description","display_order"], floors: ["name","floor_number","description","display_order"],
  leasing_spaces: ["title","space_type","floor_id","area_sqm","description","availability_status","cover_image_url","is_featured","display_order","published_at"],
  events: ["title","summary","description","venue","start_datetime","end_datetime","status","cover_image_url","is_featured","display_order","published_at"],
  promotions: ["title","summary","description","start_date","end_date","status","cover_image_url","is_featured","display_order","published_at"],
  services: ["name","description","floor_id","location_detail","status","cover_image_url","action_label","action_url","is_featured","display_order","published_at"],
  highlights: ["title","eyebrow","description","images","button_text","button_url","status","display_order"],
  mall_hours: ["day_of_week","opening_time","closing_time","is_closed"],
  site_settings: ["group","key","value","value_type","is_public"],
  inquiries: ["status","priority","assigned_user_id","internal_notes"],
};
