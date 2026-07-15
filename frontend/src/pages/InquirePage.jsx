import { useState } from "react";
import { Building2, CalendarDays, CheckCircle2, Mail, MessageSquareText, Send } from "lucide-react";
import { Link } from "react-router-dom";
import { api } from "../api";
import { PageHero } from "../components/content/ContentComponents";
import { SiteLayout } from "../components/layout/SiteLayout";
import { useSettings } from "../hooks/useContent";

const inquiryTypes = [
  "Leasing",
  "Marketing collaboration",
  "Events",
  "Mall services",
  "General inquiry",
];

export function InquirePage() {
  const settings = useSettings();
  const [result, setResult] = useState(null);
  const [error, setError] = useState("");
  const [submitting, setSubmitting] = useState(false);

  async function submit(event) {
    event.preventDefault();
    setError("");
    setSubmitting(true);

    try {
      const response = await api("/inquiries", {
        method: "POST",
        body: JSON.stringify(Object.fromEntries(new FormData(event.currentTarget))),
      });
      setResult(response);
      window.scrollTo({ top: 0, behavior: "smooth" });
    } catch (exception) {
      setError(exception.message);
    } finally {
      setSubmitting(false);
    }
  }

  return <SiteLayout>
    <main>
      <PageHero
        variant="inquire"
        eyebrow={settings["inquire.eyebrow"] || "Contact Us"}
        title={settings["inquire.title"] || "How can we help?"}
        text={settings["inquire.description"] || "Send your inquiry to the right Island Central Mactan team and track it with a reference number."}
        backgroundUrl={settings["inquire.background_url"]}
      />

      <section className="section inquiry-layout">
        <aside className="inquiry-intro">
          <p className="eyebrow">Get in touch</p>
          <h2>Tell us what you need.</h2>
          <p>Provide enough detail for our team to review your concern and respond through your preferred contact information.</p>
          <div>
            <article><MessageSquareText/><span><strong>Directed correctly</strong><small>Select an inquiry type so it reaches the appropriate team.</small></span></article>
            <article><CalendarDays/><span><strong>Plan ahead</strong><small>Mention preferred dates or schedules in your message when needed.</small></span></article>
            <article><Building2/><span><strong>For businesses too</strong><small>Company and leasing inquiries are welcome.</small></span></article>
          </div>
          <p className="inquiry-contact"><Mail/> Responses will be sent to the email address you provide.</p>
        </aside>

        {result ? <div className="inquiry-success" role="status">
          <CheckCircle2/>
          <p className="eyebrow">Inquiry received</p>
          <h2>Thank you for contacting us.</h2>
          <p>Your inquiry is now visible to the Island Central Mactan administration team.</p>
          <div>
            <span>Reference number</span>
            <strong>{result.reference_number}</strong>
            <small>Keep this number for follow-up.</small>
          </div>
          <button className="button" type="button" onClick={() => setResult(null)}>Send another inquiry</button>
          <Link to="/">Return to home</Link>
        </div> : <form className="inquiry-form" onSubmit={submit}>
          <div className="inquiry-form-heading">
            <p className="eyebrow">Inquiry details</p>
            <h2>Send us a message.</h2>
            <p>Fields marked with <em>*</em> are required.</p>
          </div>

          <div className="inquiry-form-grid">
            <label>
              <span>Full name <em>*</em></span>
              <input name="name" autoComplete="name" autoCapitalize="words" placeholder="e.g. Juan Dela Cruz" maxLength="120" required/>
              <small>Enter the name we should use in our response.</small>
            </label>

            <label>
              <span>Email address <em>*</em></span>
              <input name="email" type="email" inputMode="email" autoComplete="email" autoCapitalize="none" placeholder="e.g. juan@example.com" maxLength="190" required/>
              <small>We will send the response to this address.</small>
            </label>

            <label>
              <span>Phone number</span>
              <input name="phone" type="tel" inputMode="tel" autoComplete="tel" placeholder="e.g. 0917 123 4567" maxLength="40"/>
              <small>Optional, but useful for urgent follow-up.</small>
            </label>

            <label>
              <span>Inquiry type <em>*</em></span>
              <select name="inquiry_type" defaultValue="" required>
                <option value="" disabled>Select one</option>
                {inquiryTypes.map(type => <option key={type} value={type}>{type}</option>)}
              </select>
              <small>This sends your request to the appropriate team.</small>
            </label>

            <label className="inquiry-wide">
              <span>Message <em>*</em></span>
              <textarea name="message" rows="7" placeholder="Describe your inquiry and include any relevant details." maxLength="5000" required/>
              <small>Do not submit passwords or sensitive financial information.</small>
            </label>
          </div>

          {error && <p className="inquiry-error" role="alert">{error}</p>}
          <button className="button inquiry-submit" disabled={submitting}>
            {submitting ? "Sending inquiry…" : <><Send/> Submit inquiry</>}
          </button>
        </form>}
      </section>
    </main>
  </SiteLayout>;
}
