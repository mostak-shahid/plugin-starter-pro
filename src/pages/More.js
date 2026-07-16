import { __ } from "@wordpress/i18n";
import { Row, Col, Form, FloatingLabel, InputGroup, OverlayTrigger, Tooltip } from 'react-bootstrap';
// import { useOutletContext } from 'react-router-dom';
import { useState, useEffect } from '@wordpress/element';

import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faQuestionCircle } from '@fortawesome/free-solid-svg-icons';

import AceEditor from "react-ace";
// Load modes and theme
import "ace-builds/src-noconflict/mode-css";
import "ace-builds/src-noconflict/mode-html";
import "ace-builds/src-noconflict/mode-javascript";
import "ace-builds/src-noconflict/theme-monokai";

const More = () => {
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

            <div className="setting-unit py-4">
                <Row>
                    <Col lg={6}>
                        {
                            settingsLoading
                                ?
                                <>
                                    <div className="loading-skeleton h4" style={{ width: '60%' }}></div>
                                    <div className="loading-skeleton p" style={{ width: '70%' }}></div>
                                </>
                                :
                                <>
                                    {settingsDetails?.more?.enable_scripts?.title &&
                                        <h6 className="h6">
                                            {settingsDetails?.more?.enable_scripts?.title}
                                            {settingsDetails?.more?.enable_scripts?.hint &&
                                                <OverlayTrigger overlay={<Tooltip>{settingsDetails.more.enable_scripts.hint}</Tooltip>}>
                                                    <FontAwesomeIcon icon={faQuestionCircle} />
                                                </OverlayTrigger>
                                            }
                                        </h6>
                                    }
                                    {settingsDetails?.more?.enable_scripts?.intro &&
                                        <p className="mb-0" dangerouslySetInnerHTML={{ __html: settingsDetails?.more?.enable_scripts?.intro }} />
                                    }
                                </>
                        }
                    </Col>

                    <Col lg={6}>
                        {
                            !settingsLoading &&

                            <Form.Group>
                                {settingsDetails?.more?.enable_scripts?.before &&
                                    <Form.Label htmlFor="more-enable-scripts" dangerouslySetInnerHTML={{ __html: settingsDetails.more.enable_scripts.before }} />
                                }
                                <Form.Check
                                    id="more-enable-scripts"
                                    type="switch"
                                    // label="Check me out" 
                                    onChange={(e) => handleChange('more.enable_scripts', e.target.checked)}
                                    checked={settings?.more?.enable_scripts ? true : false}

                                />
                                {settingsDetails?.more?.enable_scripts?.after &&
                                    <Form.Text className="text-muted" dangerouslySetInnerHTML={{ __html: settingsDetails.more.enable_scripts.after }} />
                                }
                            </Form.Group>
                        }
                    </Col>

                </Row>

            </div>
            <div className="setting-unit py-4">
                <Row>
                    <Col lg={6}>
                        {
                            settingsLoading
                                ?
                                <>
                                    <div className="loading-skeleton h4" style={{ width: '60%' }}></div>
                                    <div className="loading-skeleton p" style={{ width: '70%' }}></div>
                                </>
                                :
                                <>
                                    {settingsDetails?.more?.css?.title &&
                                        <h6 className="h6">
                                            {settingsDetails?.more?.css?.title}
                                            {settingsDetails?.more?.css?.hint &&
                                                <OverlayTrigger overlay={<Tooltip>{settingsDetails.more.css.hint}</Tooltip>}>
                                                    <FontAwesomeIcon icon={faQuestionCircle} />
                                                </OverlayTrigger>
                                            }
                                        </h6>
                                    }
                                    {settingsDetails?.more?.css?.intro &&
                                        <p className="mb-0" dangerouslySetInnerHTML={{ __html: settingsDetails?.more?.css?.intro }} />
                                    }
                                </>
                        }
                    </Col>
                    {
                        !settingsLoading &&
                        <Col lg={12}>
                            <AceEditor
                                mode="css"
                                theme="monokai"
                                value={settings?.more?.css}
                                onChange={(value) => handleChange('more.css', value)}
                                name="css-editor"
                                width="100%"
                                height="200px"
                                editorProps={{ $blockScrolling: true }}
                            />
                        </Col>
                    }
                </Row>
            </div>

            <div className="setting-unit py-4">
                <Row>
                    <Col lg={6}>
                        {
                            settingsLoading
                                ?
                                <>
                                    <div className="loading-skeleton h4" style={{ width: '60%' }}></div>
                                    <div className="loading-skeleton p" style={{ width: '70%' }}></div>
                                </>
                                :
                                <>
                                    {settingsDetails?.more?.js?.title &&
                                        <h6 className="h6">
                                            {settingsDetails?.more?.js?.title}
                                            {settingsDetails?.more?.js?.hint &&
                                                <OverlayTrigger overlay={<Tooltip>{settingsDetails.more.js.hint}</Tooltip>}>
                                                    <FontAwesomeIcon icon={faQuestionCircle} />
                                                </OverlayTrigger>
                                            }
                                        </h6>
                                    }
                                    {settingsDetails?.more?.js?.intro &&
                                        <p className="mb-0" dangerouslySetInnerHTML={{ __html: settingsDetails?.more?.js?.intro }} />
                                    }
                                </>
                        }
                    </Col>
                    {
                        !settingsLoading &&
                        <Col lg={12}>
                            <AceEditor
                                mode="js"
                                theme="monokai"
                                value={settings?.more?.js}
                                onChange={(value) => handleChange('more.js', value)}
                                name="js-editor"
                                width="100%"
                                height="200px"
                                editorProps={{ $blockScrolling: true }}
                            />
                        </Col>
                    }
                </Row>
            </div>

            <div className="setting-unit py-4">
                <Row>
                    <Col lg={6}>
                        {
                            settingsLoading
                                ?
                                <>
                                    <div className="loading-skeleton h4" style={{ width: '60%' }}></div>
                                    <div className="loading-skeleton p" style={{ width: '70%' }}></div>
                                </>
                                :
                                <>
                                    {settingsDetails?.more?.header_content?.title &&
                                        <h6 className="h6">
                                            {settingsDetails?.more?.header_content?.title}
                                            {settingsDetails?.more?.header_content?.hint &&
                                                <OverlayTrigger overlay={<Tooltip>{settingsDetails.more.header_content.hint}</Tooltip>}>
                                                    <FontAwesomeIcon icon={faQuestionCircle} />
                                                </OverlayTrigger>
                                            }
                                        </h6>
                                    }
                                    {settingsDetails?.more?.header_content?.intro &&
                                        <p className="mb-0" dangerouslySetInnerHTML={{ __html: settingsDetails?.more?.header_content?.intro }} />
                                    }
                                </>
                        }
                    </Col>
                    {
                        !settingsLoading &&
                        <Col lg={12}>
                            <AceEditor
                                mode="html"
                                theme="monokai"
                                value={settings?.more?.header_content}
                                onChange={(value) => handleChange('more.header_content', value)}
                                name="header_content-editor"
                                width="100%"
                                height="200px"
                                editorProps={{ $blockScrolling: true }}
                            />
                        </Col>
                    }
                </Row>
            </div>

            <div className="setting-unit py-4">
                <Row>
                    <Col lg={6}>
                        {
                            settingsLoading
                                ?
                                <>
                                    <div className="loading-skeleton h4" style={{ width: '60%' }}></div>
                                    <div className="loading-skeleton p" style={{ width: '70%' }}></div>
                                </>
                                :
                                <>
                                    {settingsDetails?.more?.footer_content?.title &&
                                        <h6 className="h6">
                                            {settingsDetails?.more?.footer_content?.title}
                                            {settingsDetails?.more?.footer_content?.hint &&
                                                <OverlayTrigger overlay={<Tooltip>{settingsDetails.more.footer_content.hint}</Tooltip>}>
                                                    <FontAwesomeIcon icon={faQuestionCircle} />
                                                </OverlayTrigger>
                                            }
                                        </h6>
                                    }
                                    {settingsDetails?.more?.footer_content?.intro &&
                                        <p className="mb-0" dangerouslySetInnerHTML={{ __html: settingsDetails?.more?.footer_content?.intro }} />
                                    }
                                </>
                        }
                    </Col>
                    {
                        !settingsLoading &&
                        <Col lg={12}>
                            <AceEditor
                                mode="html"
                                theme="monokai"
                                value={settings?.more?.footer_content}
                                onChange={(value) => handleChange('more.footer_content', value)}
                                name="footer_content-editor"
                                width="100%"
                                height="200px"
                                editorProps={{ $blockScrolling: true }}
                            />
                        </Col>
                    }
                </Row>
            </div>
        </>
    );
};

export default More;