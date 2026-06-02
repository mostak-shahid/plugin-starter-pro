import ContactForm from './components/ContactForm';
import PluginNews from './components/PluginNews';
import menuItems from './data/menuItems';

// Attach component definition to global window scope safely
window.PluginStarterProComponents = {
    ...window.PluginStarterProComponents,
    ContactForm: ContactForm,
    PluginNews: PluginNews,
    menuItems: menuItems,
};      
