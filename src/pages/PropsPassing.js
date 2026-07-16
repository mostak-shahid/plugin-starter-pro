import { __ } from "@wordpress/i18n";
import { Button, Card, Row, Col, Form } from 'react-bootstrap';
// import { useOutletContext } from 'react-router-dom';
import { useState, useEffect } from '@wordpress/element';

export default function PropsPassing({settings, settingsDetails, settingsLoading, handleChange, settingsReload, setSettingsReload}) {
    return (
        <>

            <div className="setting-unit py-4">
                <Row>
                    <Col lg={6}>
                        <h4 className="h4">{__("Enable Scripts", "plugin-starter-pro")}</h4>
                        <p>{__("Enable/Disable \"Scripts\" functionalities", "plugin-starter-pro")}</p>
                    </Col>
                    {
                        !settingsLoading &&
                        <Col lg={6}>
                            <Form.Group>
                                <Form.Check
                                    id="inputs_props_passing_switch"
                                    type="switch"
                                    // label="Check me out" 
                                    onChange={(e) => handleChange('inputs.props_passing.switch', e.target.checked)}
                                    checked={settings?.inputs?.props_passing?.switch ? true : false}

                                />
                            </Form.Group>
                        </Col>
                    }
                </Row>
            </div>
        </>
    )
}
