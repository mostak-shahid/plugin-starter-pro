import { useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

export default function ContactForm() {
    const [formData, setFormData] = useState({ name: '', email: '', message: '' });
    const [status, setStatus] = useState({ type: '', msg: '' });
    const [submitting, setSubmitting] = useState(false);

    const handleSubmit = async (e) => {
        e.preventDefault();
        setSubmitting(true);
        setStatus({ type: '', msg: '' });

        try {
            // Sends data directly to the native WordPress custom REST API endpoint
            const response = await apiFetch({
                path: '/plugin-starter-pro/v1/feedback',
                method: 'POST',
                data: formData
            });

            if (response.success) {
                setStatus({ type: 'success', msg: 'Feedback submitted successfully!' });
                setFormData({ name: '', email: '', message: '' });
            }
        } catch (error) {
            setStatus({ 
                type: 'error', 
                msg: error.message || 'An error occurred while transmitting your request.' 
            });
        } finally {
            setSubmitting(false);
        }
    };

    return (
        <form onSubmit={handleSubmit} className="mt-4 space-y-4 max-w-md">
            <div>
                <label className="block text-sm font-medium text-gray-700">Name</label>
                <input 
                    type="text" required 
                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    value={formData.name}
                    onChange={(e) => setFormData({...formData, name: e.target.value})}
                />
            </div>
            <div>
                <label className="block text-sm font-medium text-gray-700">Email</label>
                <input 
                    type="email" required 
                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    value={formData.email}
                    onChange={(e) => setFormData({...formData, email: e.target.value})}
                />
            </div>
            <div>
                <label className="block text-sm font-medium text-gray-700">Message</label>
                <textarea 
                    rows="4" required 
                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    value={formData.message}
                    onChange={(e) => setFormData({...formData, message: e.target.value})}
                />
            </div>

            {status.msg && (
                <div className={`p-3 rounded text-sm ${status.type === 'success' ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800'}`}>
                    {status.msg}
                </div>
            )}

            <button 
                type="submit" disabled={submitting}
                className="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
            >
                {submitting ? 'Sending...' : 'Submit Premium Request'}
            </button>
        </form>
    );
}

/*
Uses
const [ProContactForm, setProContactForm] = useState(null);

    useEffect(() => {
        // Check if the Pro version has loaded its global component hook
        if (window.PluginStarterProComponents && window.PluginStarterProComponents.ContactForm) {
            setProContactForm(() => window.PluginStarterProComponents.ContactForm);
        }
        // console.log('Feedback component mounted. ProContactForm available:', !!window.PluginStarterProComponents?.ContactForm);
    }, []);

    return (        
        <>  
            {ProContactForm ? (
                // If Pro is active, render the Pro Form component
                <ProContactForm />
            ) : (
                // Fallback layout if only Free is active
                
                <p>
                    Please upgrade to the Pro Version to access the integrated contact and diagnostics desk.
                </p>
            )}   
        </>
    );
*/