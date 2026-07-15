import { useMemo, useState } from "react";
import { Link } from "react-router-dom";
import { Car, Check, ChevronLeft, ChevronRight, Clock3, MapPinned, PhilippinePeso, RotateCcw, Route, Users } from "lucide-react";
import { SiteLayout } from "../components/layout/SiteLayout";
import { Loading, PageHero } from "../components/content/ContentComponents";
import { useContent } from "../hooks/useContent";

const questions = [
  { key:"duration", title:"How much time do you have?", hint:"We will keep your itinerary realistic.", icon:Clock3, options:[["60","About 1 hour"],["120","2 hours"],["240","Half day"],["360","Most of the day"]] },
  { key:"budget", title:"What is your budget?", hint:"Choose an estimated total spend for your visit.", icon:PhilippinePeso, options:[["500","Up to ₱500"],["1500","₱500–₱1,500"],["3000","₱1,500–₱3,000"],["5000","₱3,000+"]] },
  { key:"company", title:"Who are you visiting with?", hint:"This helps us choose suitable stops.", icon:Users, options:[["family","Family"],["friends","Friends"],["alone","Just me"]] },
  { key:"goal", title:"What would make this visit worthwhile?", hint:"Pick your main interest—we will still add a little variety.", icon:MapPinned, options:[["food","Eat and try something new"],["shopping","Shop and run errands"],["fun","Relax and have fun"],["mix","A balanced mall day"]] },
  { key:"parking", title:"Do you need parking?", hint:"We can include arrival and parking time.", icon:Car, options:[["yes","Yes, I am driving"],["no","No parking needed"]] },
  { key:"walking", title:"Would you prefer walking less?", hint:"Choose a more compact route if mobility or convenience matters.", icon:Route, options:[["less","Yes, keep stops close"],["regular","No preference"]] },
];

function includesAny(value, words){return words.some(word=>value.includes(word))}

function buildPlan(answers, tenants, categories){
  const categoryById=Object.fromEntries(categories.map(category=>[category.id,category.name]));
  const enriched=tenants.map(tenant=>({...tenant,type:categoryById[tenant.category_id]||"Mall stop",search:`${tenant.name} ${categoryById[tenant.category_id]||""} ${tenant.description||""}`.toLowerCase()}));
  const dining=enriched.filter(item=>includesAny(item.search,["food","dining","restaurant","cafe","coffee"]));
  const entertainment=enriched.filter(item=>includesAny(item.search,["entertainment","cinema","game","leisure"]));
  const essentials=enriched.filter(item=>includesAny(item.search,["retail","essential","grocery","health","wellness","service"]));
  const family=enriched.filter(item=>includesAny(item.search,["family","kid","toy","entertainment","food"]));
  const companyMix=answers.company==="family"?[...family,...dining,...essentials]:answers.company==="friends"?[...dining,...entertainment,...essentials]:[...dining,...essentials,...entertainment];
  const source=answers.goal==="food"?[...dining,...entertainment,...companyMix]:answers.goal==="shopping"?[...essentials,...dining,...companyMix]:answers.goal==="fun"?[...entertainment,...dining,...companyMix]:companyMix;
  const unique=[...new Map([...source,...enriched].map(item=>[item.id,item])).values()];
  const available=Math.max(45,Number(answers.duration)-(answers.parking==="yes"?20:0));
  const stopCount=available<90?2:available<180?3:available<300?4:5;
  const stops=unique.slice(0,stopCount);
  const visitMinutes=Math.max(20,Math.floor(available/Math.max(stops.length,1)));
  const budget=Number(answers.budget);
  return {
    stops:stops.map((stop,index)=>({...stop,minutes:visitMinutes,number:index+1})),
    arrival:answers.parking==="yes"?"Allow about 20 minutes to enter, park, and reach your first stop.":"Start near the mall entrance and head directly to your first stop.",
    walking:answers.walking==="less"?"Stops are kept compact. Confirm the nearest lift or escalator at the information desk.":"This route allows more time to explore between stops.",
    spend:budget>=5000?"₱3,000 or more":budget>=3000?"₱1,500–₱3,000":budget>=1500?"₱500–₱1,500":"up to ₱500",
    total:Number(answers.duration),
  };
}

export function VisitPlannerPage(){
  const [tenants,loadingTenants]=useContent("tenants"),[categories,loadingCategories]=useContent("categories");
  const [step,setStep]=useState(0),[answers,setAnswers]=useState({}),[complete,setComplete]=useState(false);
  const question=questions[step],selected=answers[question?.key];
  const plan=useMemo(()=>complete?buildPlan(answers,tenants,categories):null,[complete,answers,tenants,categories]);
  function choose(value){setAnswers(current=>({...current,[question.key]:value}))}
  function next(){if(!selected)return;if(step<questions.length-1)setStep(value=>value+1);else setComplete(true)}
  function restart(){setAnswers({});setStep(0);setComplete(false)}
  return <SiteLayout><main><PageHero variant="planner" eyebrow="Plan your visit" title="Make every mall visit count." text="Tell us what your day looks like and we will create a simple itinerary for Island Central Mactan."/>
    <section className="section visit-planner-section">
      {loadingTenants||loadingCategories?<Loading/>:!complete?<div className="planner-shell">
        <aside className="planner-progress"><p className="eyebrow">Your preferences</p><strong>Question {step+1} of {questions.length}</strong><div className="planner-progress-track"><i style={{width:`${((step+1)/questions.length)*100}%`}}/></div><ol>{questions.map((item,index)=><li className={index===step?"active":index<step?"done":""} key={item.key}><span>{index<step?<Check/>:index+1}</span>{item.title}</li>)}</ol></aside>
        <div className="planner-question"><div className="planner-question-icon"><question.icon/></div><p className="eyebrow">A better visit starts here</p><h2>{question.title}</h2><p>{question.hint}</p><div className="planner-options" role="radiogroup" aria-label={question.title}>{question.options.map(([value,label])=><button type="button" role="radio" aria-checked={selected===value} className={selected===value?"selected":""} onClick={()=>choose(value)} key={value}><span>{selected===value?<Check/>:null}</span>{label}</button>)}</div><div className="planner-controls"><button type="button" className="planner-back" disabled={step===0} onClick={()=>setStep(value=>value-1)}><ChevronLeft/> Back</button><button type="button" className="button" disabled={!selected} onClick={next}>{step===questions.length-1?"Create my plan":"Continue"}<ChevronRight/></button></div></div>
      </div>:<div className="planner-result"><div className="planner-result-heading"><div><p className="eyebrow">Your suggested visit</p><h2>A plan made for your day.</h2><p>Allow approximately {plan.total/60} {plan.total===60?"hour":"hours"}, with a spending guide of {plan.spend}.</p></div><button type="button" onClick={restart}><RotateCcw/> Start again</button></div><div className="planner-summary"><article><Car/><div><strong>Arrival</strong><p>{plan.arrival}</p></div></article><article><Route/><div><strong>Getting around</strong><p>{plan.walking}</p></div></article></div><div className="planner-itinerary"><div className="planner-route-line"/>{plan.stops.map(stop=><article key={stop.id}><span>{stop.number}</span><div><small>{stop.type}</small><h3>{stop.name}</h3><p>{stop.location_detail||"Check the mall directory for its current location."}</p></div><strong><Clock3/> {stop.minutes} min</strong></article>)}</div><div className="planner-result-actions"><Link className="button" to="/directory"><MapPinned/> View tenant directory</Link><Link className="button outline" to="/services">View mall services</Link></div><p className="planner-note">Suggested times and expenses are estimates. Store hours and availability may change.</p></div>}
    </section>
  </main></SiteLayout>;
}
