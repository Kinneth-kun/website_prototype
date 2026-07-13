const socials = [
  ["Facebook", "social.facebook", "https://www.facebook.com/islandcentralmactanofficial", "M14 8.4V6.8c0-.8.2-1.3 1.4-1.3H17V2.6c-.8-.1-1.7-.2-2.5-.2-2.6 0-4.4 1.6-4.4 4.5v1.5H7.2v3.3h2.9v8.9H14v-8.9h2.8l.4-3.3H14Z"],
  ["Instagram", "social.instagram", "https://www.instagram.com/islandcentralmactan", "M7.8 2.5h8.4c2.9 0 5.3 2.4 5.3 5.3v8.4c0 2.9-2.4 5.3-5.3 5.3H7.8c-2.9 0-5.3-2.4-5.3-5.3V7.8c0-2.9 2.4-5.3 5.3-5.3Zm0 1.9c-1.9 0-3.4 1.5-3.4 3.4v8.4c0 1.9 1.5 3.4 3.4 3.4h8.4c1.9 0 3.4-1.5 3.4-3.4V7.8c0-1.9-1.5-3.4-3.4-3.4H7.8Zm4.2 3.2a4.4 4.4 0 1 1 0 8.8 4.4 4.4 0 0 1 0-8.8Z"],
  ["TikTok", "social.tiktok", "https://www.tiktok.com/@islandcentralmactanmall", "M15.7 2.5c.4 2.7 1.9 4.3 4.5 4.5v3.2c-1.6.1-3-.4-4.4-1.3v5.9c0 3.8-2.1 6.2-5.5 6.2-3.1 0-5.2-2.1-5.2-5.1 0-3.3 2.6-5.6 6.3-5.2v3.3c-1.6-.3-2.8.5-2.8 1.8 0 1.1.8 1.8 1.9 1.8 1.3 0 2.1-.8 2.1-2.5V2.5h3.1Z"],
  ["Indeed Jobs", "social.indeed", "https://ph.indeed.com/cmp/Geege-Central-Mall,-Inc.?campaignid=mobvjcmp&from=mobviewjob&tk=1jsb5sktmh12a800&fromjk=f5ec568918477b9e", "M11.1 9.2h3.5v11.3h-3.5V9.2Zm1.7-5.7c1.1 0 2 .8 2 1.9s-.9 1.9-2 1.9-2-.8-2-1.9.9-1.9 2-1.9Zm6.5 17h-3.4v-5.7c0-1.5-.5-2.5-1.8-2.5-1 0-1.6.7-1.9 1.3-.1.2-.1.6-.1.9v6h-3.4V9.7h3.4v1.5c.5-.7 1.3-1.8 3.1-1.8 2.3 0 4 1.5 4 4.7v6.4Z"],
];

export function SocialLinks({settings={}}) {
  return <div className="socials" aria-label="Social media">{socials.map(([label,key,fallback,path]) =>
    <a key={label} href={settings[key]||fallback} target="_blank" rel="noreferrer" aria-label={label}><svg viewBox="0 0 24 24" aria-hidden="true"><path d={path}/></svg></a>
  )}</div>;
}
