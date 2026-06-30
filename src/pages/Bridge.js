import { __ } from "@wordpress/i18n";
import { Button, Card, Row, Col, Form } from 'react-bootstrap';
// import { useOutletContext } from 'react-router-dom';
import { useState, useEffect } from '@wordpress/element';

const Bridge = () => {
    // const { settings, settingsDetails, settingsLoading, handleChange } = useOutletContext();
    // This will work even if the bundle is isolated
    // const bridge = window.PluginStarterBridge || {}; 
    // const { settings, settingsDetails, settingsLoading, handleChange } = bridge;

    // 1. Initialize with empty data; we will fill it once the bridge is ready
    const [data, setData] = useState({});
    const [isBridgeReady, setIsBridgeReady] = useState(false);

    useEffect(() => {
        let unsubscribe;

        // Function to initialize the subscription
        const initBridge = () => {
            if (window.PluginStarterBridge) {
                // Get initial data
                setData(window.PluginStarterBridge.get());
                setIsBridgeReady(true);

                // Subscribe to future updates
                unsubscribe = window.PluginStarterBridge.subscribe((newData) => {
                    setData(newData);
                });
            }
        };

        // Attempt initialization immediately
        initBridge();

        // If not ready, poll until the bridge is available
        const interval = !window.PluginStarterBridge ? setInterval(() => {
            if (window.PluginStarterBridge) {
                initBridge();
                clearInterval(interval);
            }
        }, 50) : null;

        return () => {
            if (interval) clearInterval(interval);
            if (unsubscribe) unsubscribe();
        };
    }, []);

    // 2. Destructure safely
    const { settings, settingsDetails, settingsLoading, handleChange } = data;

    if (!isBridgeReady) {
        return <div>Loading...</div>;
    }


    return (
        <>
            {console.log(settings)}
            <div className="setting-unit py-4">
                <Row>
                    <Col lg={6}>
                        <h4 className="h4">{__("Enable Scripts", "plugin-starter")}</h4>
                        <p>{__("Enable/Disable \"Scripts\" functionalities", "plugin-starter")}</p>
                    </Col>
                    {
                        !settingsLoading &&
                        <Col lg={6}>
                            <Form.Group>
                                <Form.Check
                                    id="inputs_bridge_switch"
                                    type="switch"
                                    // label="Check me out" 
                                    onChange={(e) => handleChange('inputs.bridge.switch', e.target.checked)}
                                    checked={settings?.inputs?.bridge?.switch ? true : false}

                                />
                            </Form.Group>
                        </Col>
                    }
                </Row>
            </div>
        </>
    );
};

export default Bridge;