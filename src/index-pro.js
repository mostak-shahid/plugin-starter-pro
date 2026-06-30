import ContactForm from './components/ContactForm';
import PluginNews from './components/PluginNews';
import More from './pages/More';
import PropsPassing from './pages/PropsPassing';
import Bridge from './pages/Bridge';
import menuItems from './data/menuItems';

// Attach component definition to global window scope safely
window.PluginStarterProComponents = {
    ...window.PluginStarterProComponents,
    ContactForm: ContactForm,
    PluginNews: PluginNews,
    More: More,
    PropsPassing: PropsPassing,
    Bridge: Bridge,
    menuItems: menuItems,
};      
