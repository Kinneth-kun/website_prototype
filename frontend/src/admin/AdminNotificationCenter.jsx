import { useCallback, useEffect, useRef, useState } from "react";
import { Link } from "react-router-dom";
import { Bell, Check, CheckCheck, CircleAlert, Inbox, RefreshCw } from "lucide-react";
import { api } from "../api";
import "./AdminNotificationCenter.css";

const POLL_INTERVAL = 30_000;

function asNumber(value, fallback = 0) {
  const parsed = Number(value);
  return Number.isFinite(parsed) ? parsed : fallback;
}

function normalizeNotification(item, index) {
  const readAt = item.read_at ?? item.readAt ?? null;
  const isRead = Boolean(item.is_read ?? item.isRead ?? readAt);

  return {
    ...item,
    id: item.id ?? item.notification_id ?? `notification-${index}`,
    title: item.title || item.subject || item.type_label || "New notification",
    message: item.message || item.body || item.description || "There is a new admin update.",
    actionUrl: item.action_url || item.actionUrl || item.url || "",
    createdAt: item.created_at || item.createdAt || item.timestamp || null,
    isRead,
    readAt,
  };
}

function normalizeResponse(payload) {
  const envelope = payload?.data && !Array.isArray(payload.data) ? payload.data : payload;
  const collection = Array.isArray(payload)
    ? payload
    : Array.isArray(payload?.data)
      ? payload.data
      : Array.isArray(payload?.notifications)
        ? payload.notifications
        : Array.isArray(envelope?.notifications)
          ? envelope.notifications
          : Array.isArray(envelope?.data)
            ? envelope.data
            : [];
  const notifications = collection.map(normalizeNotification);
  const suppliedCount = payload?.unread_count ?? payload?.unreadCount
    ?? envelope?.unread_count ?? envelope?.unreadCount
    ?? payload?.meta?.unread_count ?? envelope?.meta?.unread_count;

  return {
    notifications,
    unreadCount: suppliedCount == null
      ? notifications.filter(notification => !notification.isRead).length
      : asNumber(suppliedCount),
  };
}

function timeAgo(value) {
  if (!value) return "Just now";
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return "Recently";
  const difference = date.getTime() - Date.now();
  const absolute = Math.abs(difference);
  const formatter = new Intl.RelativeTimeFormat("en", { numeric: "auto" });
  if (absolute < 60_000) return "Just now";
  if (absolute < 3_600_000) return formatter.format(Math.round(difference / 60_000), "minute");
  if (absolute < 86_400_000) return formatter.format(Math.round(difference / 3_600_000), "hour");
  if (absolute < 604_800_000) return formatter.format(Math.round(difference / 86_400_000), "day");
  return new Intl.DateTimeFormat("en-PH", { month: "short", day: "numeric", year: "numeric" }).format(date);
}

function NotificationDestination({ notification, children, onOpen }) {
  if (!notification.actionUrl) {
    return <button type="button" className="admin-notification-copy" onClick={onOpen}>{children}</button>;
  }

  if (notification.actionUrl.startsWith("/")) {
    return <Link className="admin-notification-copy" to={notification.actionUrl} onClick={onOpen}>{children}</Link>;
  }

  return <a className="admin-notification-copy" href={notification.actionUrl} onClick={onOpen}>{children}</a>;
}

/**
 * Reusable admin notification bell. Its parent only needs to render
 * <AdminNotificationCenter /> inside an authenticated admin screen.
 */
export default function AdminNotificationCenter({ pollInterval = POLL_INTERVAL }) {
  const rootRef = useRef(null);
  const triggerRef = useRef(null);
  const requestRef = useRef(0);
  const [open, setOpen] = useState(false);
  const [notifications, setNotifications] = useState([]);
  const [unreadCount, setUnreadCount] = useState(0);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [error, setError] = useState("");
  const [updatingIds, setUpdatingIds] = useState(() => new Set());
  const [markingAll, setMarkingAll] = useState(false);

  const loadNotifications = useCallback(async ({ quiet = false } = {}) => {
    const requestId = ++requestRef.current;
    if (quiet) setRefreshing(true);
    else setLoading(true);
    try {
      const result = normalizeResponse(await api("/admin/notifications"));
      if (requestId !== requestRef.current) return;
      setNotifications(result.notifications);
      setUnreadCount(result.unreadCount);
      setError("");
    } catch (exception) {
      if (requestId === requestRef.current) setError(exception.message || "Notifications could not be loaded.");
    } finally {
      if (requestId === requestRef.current) {
        setLoading(false);
        setRefreshing(false);
      }
    }
  }, []);

  useEffect(() => {
    loadNotifications();
    const poll = () => {
      if (document.visibilityState === "visible") loadNotifications({ quiet: true });
    };
    const timer = window.setInterval(poll, Math.max(15_000, pollInterval));
    document.addEventListener("visibilitychange", poll);
    return () => {
      window.clearInterval(timer);
      document.removeEventListener("visibilitychange", poll);
      requestRef.current += 1;
    };
  }, [loadNotifications, pollInterval]);

  useEffect(() => {
    if (!open) return undefined;
    const closeOutside = event => {
      if (!rootRef.current?.contains(event.target)) setOpen(false);
    };
    const closeWithKeyboard = event => {
      if (event.key !== "Escape") return;
      setOpen(false);
      triggerRef.current?.focus();
    };
    document.addEventListener("pointerdown", closeOutside);
    document.addEventListener("keydown", closeWithKeyboard);
    return () => {
      document.removeEventListener("pointerdown", closeOutside);
      document.removeEventListener("keydown", closeWithKeyboard);
    };
  }, [open]);

  async function markRead(notification) {
    if (notification.isRead || updatingIds.has(notification.id)) return;
    setUpdatingIds(current => new Set(current).add(notification.id));
    setNotifications(current => current.map(item => item.id === notification.id ? { ...item, isRead: true } : item));
    setUnreadCount(current => Math.max(0, current - 1));
    try {
      await api(`/admin/notifications/${notification.id}/read`, { method: "PUT" });
      setError("");
    } catch (exception) {
      setNotifications(current => current.map(item => item.id === notification.id ? { ...item, isRead: false } : item));
      setUnreadCount(current => current + 1);
      setError(exception.message || "The notification could not be marked as read.");
    } finally {
      setUpdatingIds(current => {
        const next = new Set(current);
        next.delete(notification.id);
        return next;
      });
    }
  }

  async function markAllRead() {
    if (!unreadCount || markingAll) return;
    const previous = notifications;
    const previousCount = unreadCount;
    setMarkingAll(true);
    setNotifications(current => current.map(notification => ({ ...notification, isRead: true })));
    setUnreadCount(0);
    try {
      await api("/admin/notifications/read-all", { method: "PUT" });
      setError("");
    } catch (exception) {
      setNotifications(previous);
      setUnreadCount(previousCount);
      setError(exception.message || "Notifications could not be marked as read.");
    } finally {
      setMarkingAll(false);
    }
  }

  function openNotification(notification) {
    markRead(notification);
    setOpen(false);
  }

  const badge = unreadCount > 99 ? "99+" : String(unreadCount);

  return <div className="admin-notification-center" ref={rootRef}>
    <button
      ref={triggerRef}
      type="button"
      className={`admin-notification-trigger${open ? " is-open" : ""}`}
      aria-label={unreadCount ? `Notifications, ${unreadCount} unread` : "Notifications"}
      aria-haspopup="dialog"
      aria-expanded={open}
      aria-controls="admin-notification-panel"
      onClick={() => setOpen(value => !value)}
    >
      <Bell aria-hidden="true"/>
      {unreadCount > 0 && <span className="admin-notification-badge" aria-hidden="true">{badge}</span>}
    </button>

    {open && <section
      id="admin-notification-panel"
      className="admin-notification-panel"
      role="dialog"
      aria-modal="false"
      aria-labelledby="admin-notification-heading"
    >
      <header>
        <div>
          <h2 id="admin-notification-heading">Notifications</h2>
          <p>{unreadCount ? `${unreadCount} unread update${unreadCount === 1 ? "" : "s"}` : "You’re all caught up"}</p>
        </div>
        <button
          type="button"
          className="admin-notification-read-all"
          onClick={markAllRead}
          disabled={!unreadCount || markingAll}
        >
          <CheckCheck aria-hidden="true"/>
          {markingAll ? "Updating…" : "Mark all read"}
        </button>
      </header>

      {error && <div className="admin-notification-error" role="alert">
        <CircleAlert aria-hidden="true"/>
        <span>{error}</span>
        <button type="button" onClick={() => loadNotifications({ quiet: true })}>Retry</button>
      </div>}

      <div className="admin-notification-list" role="list" aria-busy={loading}>
        {loading && notifications.length === 0 && Array.from({ length: 3 }, (_, index) => <div className="admin-notification-skeleton" key={index} aria-hidden="true"><i/><span/><span/></div>)}

        {!loading && notifications.length === 0 && <div className="admin-notification-empty">
          <span><Inbox aria-hidden="true"/></span>
          <strong>No notifications yet</strong>
          <p>New inquiries and important admin updates will appear here.</p>
        </div>}

        {notifications.map(notification => <article
          key={notification.id}
          className={`admin-notification-item${notification.isRead ? "" : " is-unread"}`}
          role="listitem"
        >
          <span className="admin-notification-icon" aria-hidden="true"><Bell/></span>
          <NotificationDestination notification={notification} onOpen={() => openNotification(notification)}>
            <strong>{notification.title}</strong>
            <span>{notification.message}</span>
            <time dateTime={notification.createdAt || undefined}>{timeAgo(notification.createdAt)}</time>
          </NotificationDestination>
          {!notification.isRead && <button
            type="button"
            className="admin-notification-read-one"
            aria-label={`Mark “${notification.title}” as read`}
            title="Mark as read"
            onClick={() => markRead(notification)}
            disabled={updatingIds.has(notification.id)}
          >
            <Check aria-hidden="true"/>
          </button>}
        </article>)}
      </div>

      {notifications.length > 0 && <footer>
        <button type="button" onClick={() => loadNotifications({ quiet: true })} disabled={refreshing}>
          <RefreshCw className={refreshing ? "is-spinning" : ""} aria-hidden="true"/>
          {refreshing ? "Refreshing…" : "Refresh notifications"}
        </button>
      </footer>}
    </section>}
  </div>;
}

export { normalizeResponse };
