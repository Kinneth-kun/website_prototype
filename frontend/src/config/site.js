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
  ["/visit-planner", "Visit Planner"],
];

export const adminResources = [
  ["tenants", "Tenants", Store], ["categories", "Categories", Tags],
  ["floors", "Floors", Building2], ["leasing_spaces", "Leasing spaces", Building2],
  ["events", "Events", CalendarDays], ["promotions", "Promotions", Tags],
  ["services", "Services", Wrench], ["highlights", "Media highlights", CalendarDays],
  ["inquiries", "Inquiries", Send], ["mall_hours", "Mall hours", CalendarDays],
  ["media", "Media library", CalendarDays],
];

export const adminFields = {
  tenants: ["trade_name","industry_name","company_address","email_address","nature_of_business","approved_products","picture_of_branches","picture_of_menu"],
  categories: ["name","description"], floors: ["name","floor_number","description"],
  leasing_spaces: ["branch_id","unit_code","unit_name","floor_level","location_description","floor_area_sqm","status"],
  events: ["title","summary","venue","start_datetime","end_datetime","status","cover_image_url","is_featured","published_at"],
  promotions: ["title","summary","start_date","end_date","status","cover_image_url","is_featured","published_at"],
  services: ["name","description","floor_id","location_detail","status","cover_image_url","action_label","action_url","is_featured","published_at"],
  highlights: ["title","eyebrow","description","images","button_text","button_url","status","display_order"],
  mall_hours: ["day_of_week","opening_time","closing_time","is_closed"],
  inquiries: ["status","priority","assigned_user_id","internal_notes"],
};
