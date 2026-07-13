import { Navigate, Route, Routes } from "react-router-dom";
import { AboutPage, DirectoryPage, EventsPage, HomePage, InquirePage, LeasingPage, MallPage, PolicyPage, ServicesPage } from "./pages/PublicPages";
import { AdminDashboardPage, AdminLoginPage, AdminResourcePage } from "./admin/AdminPages";

export default function App() {
  return <Routes>
    <Route path="/" element={<HomePage/>}/>
    <Route path="/mall" element={<MallPage/>}/>
    <Route path="/directory" element={<DirectoryPage/>}/>
    <Route path="/leasing" element={<LeasingPage/>}/>
    <Route path="/events" element={<EventsPage/>}/>
    <Route path="/about" element={<AboutPage/>}/>
    <Route path="/services" element={<ServicesPage/>}/>
    <Route path="/inquire" element={<InquirePage/>}/>
    <Route path="/privacy-policy" element={<PolicyPage type="privacy-policy"/>}/>
    <Route path="/terms-of-service" element={<PolicyPage type="terms-of-service"/>}/>
    <Route path="/cookies-policy" element={<PolicyPage type="cookies-policy"/>}/>
    <Route path="/admin" element={<AdminLoginPage/>}/>
    <Route path="/admin/dashboard" element={<AdminDashboardPage/>}/>
    <Route path="/admin/manage/:resource" element={<AdminResourcePage/>}/>
    <Route path="*" element={<Navigate to="/" replace/>}/>
  </Routes>;
}
