import { useState, useEffect } from '@wordpress/element';
import { __ } from "@wordpress/i18n";
import apiFetch from "@wordpress/api-fetch";
import { useCallback } from '@wordpress/element';
import {Button, Badge, ProgressBar} from 'react-bootstrap';
import { FaStar, FaStarHalfAlt, FaRegStar, FaChartBar, FaUsers, FaWordpress } from "react-icons/fa";

import FontAwesomeRating from './FontAwesomeRating';
const BootstrapProgressRating = ({ rating }) => {
    const numericRating = parseFloat((rating / 20).toFixed(2));
    // Convert 0-5 scale to percentage (e.g., 4.5 out of 5 = 90%)
    const percentage = (numericRating / 5) * 100;

    return (
        <div style={{ width: '150px' }}>
            <ProgressBar
                now={percentage}
                variant="warning"
                label={`${numericRating} / 5`}
                visuallyHidden // Removes text inside the bar for a cleaner look
            />
            <small className="d-none text-muted">{numericRating} out of 5</small>
        </div>
    );
};
// const FontAwesomeRating = ({ rating }) => {
//     // Convert your 0-100 score to a 0-5 scale
//     const numericRating = parseFloat((rating / 20).toFixed(2));

//     return (
//         <div className="d-flex align-items-center gap-1 text-warning" aria-label={`Rating: ${numericRating} out of 5`}>
//             {[1, 2, 3, 4, 5].map((starIndex) => {
//                 if (numericRating >= starIndex) {
//                     // Full Star
//                     return <FaStar key={starIndex} className="star-rating solid-full-star"  />;
//                 } else if (numericRating > starIndex - 1 && numericRating < starIndex) {
//                     // Half Star
//                     return <FaStarHalfAlt key={starIndex} className="star-rating half-full-star"  />;
//                 } else {
//                     // Empty Star
//                     return <FaRegStar key={starIndex} className="star-rating border-full-star" />;
//                 }
//             })}
//             <span className="d-none ms-1 text-muted small">{numericRating}</span>
//         </div>
//     );
// };
export default function PluginCard(plugin) {
    const { image, name, short_description, author, plugin_source = 'internal', plugin_slug = '', plugin_file = '', download_url = '', version = '1.0.0', rating = '0', num_ratings = '0', active_installs = '0', tested } = plugin;
    const [pluginStatus, setPluginStatus] = useState("checking");
    const [errorMessage, setErrorMessage] = useState("");

    useEffect(() => {
        const fetchPluginStatus = async () => {
            try {
                const params = new URLSearchParams({
                    plugin: plugin_file,
                });
                const result = await apiFetch({
                    path: `/plugin-starter-pro/v1/plugin/status?${params.toString()}`,
                    method: 'GET'
                });
                // console.log(result);
                setPluginStatus(result?.status)
                
            } catch (err) {
                console.error('API error:', err);
            }
        };
        fetchPluginStatus();
    }, []);

    const getButtonLabel = () => {
        switch (pluginStatus) {
            case "checking":
                return __("Checking...", "plugin-starter-pro");
            case "uninstalled":
                return __("Install Now", "plugin-starter-pro");
            case "installing":
                return __("Installing...", "plugin-starter-pro");
            case "deactivated":
                return __("Activate", "plugin-starter-pro");
            case "activating":
                return __("Activating...", "plugin-starter-pro");
            case "activated":
                return __("Activated", "plugin-starter-pro");
            case "error":
                return __("Try Again", "plugin-starter-pro");
            default:
                return __("Install Now", "plugin-starter-pro");
        }
    };
    const handleButtonClick = () => {
        switch (pluginStatus) {
            case "uninstalled":
                installPlugin();
                break;
            case "deactivated":
                activatePlugin();
                break;
            default:
                break;
        }
    };

    const installPlugin = async () => {
        setPluginStatus("installing");
        setErrorMessage("");        
        try {
            const result = await apiFetch({
                path: '/plugin-starter-pro/v1/plugin/install',
                method: 'POST',
                data: {
                    source: plugin_slug, 
                },
            });
            
            // Only set to installed if the request actually succeeds
            setPluginStatus("installed");
        } catch (error) {
            setErrorMessage(error.message);
            setPluginStatus("failed"); // FIX: Keep status as failed on catch
        }
    };


    const activatePlugin = async () => {
        setPluginStatus("activating");
        setErrorMessage("");
        try {
            const result = await apiFetch({
                path: '/plugin-starter-pro/v1/plugin/action',
                method: 'POST',
                data: {
                    plugin: plugin_file, 
                    plugin_action: 'activate'
                },
            });
            
            // Only set to installed if the request actually succeeds
            setPluginStatus("activated");
        } catch (error) {
            setErrorMessage(error.message);
        } 
    };
    const isButtonDisabled = ["checking", "installing", "activating", "activated"].includes(
        pluginStatus,
    );
    return (
        <div className="plugin-starter-plugin-card p-4 border">
            <div className="d-flex align-items-center gap-2">
                <img
                    alt={name}
                    src={image}
                    style={{ flex: '0 0 80px', maxWidth: '80px' }}
                    className="plugin-image"
                />
                <div>
                    <a className="plugin-title mb-1" href={`https://wordpress.org/plugins/${plugin_slug}/`} target="_blank"><h6 className="h6" style={{ fontSize: 18, marginBottom: 0 }} >{name}</h6></a>
                    <div className="d-flex align-items-center gap-2">
                        {/* <Rating allowHalf defaultValue={(rating/20).toFixed(2)} disabled/>    */}
                        {/* <BootstrapProgressRating rating={rating} /> */}
                        <FontAwesomeRating rating={rating} />
                        <span className="text-muted small">({num_ratings})</span>
                    </div>
                </div>
            </div>
            <div className="action-button mt-2">
                <Button 
                    variant="outline-primary" 
                    className="rounded-0"
                    onClick={handleButtonClick}
                    disabled={isButtonDisabled}
                >
                    {getButtonLabel()}
                </Button>
            </div>
            <div className="mt-3">
                <div className="short-description">{short_description}</div>
            </div>
            <div className="d-flex justify-content-between mt-2">
                <div className="d-flex align-items-center gap-1"><FaUsers /><span className="plugin-authors" dangerouslySetInnerHTML={{ __html: author }} /></div>
                <Badge bg="light" text="dark">{version}</Badge>
            </div>
            <div className="d-flex justify-content-between mt-1">
                <div className="d-flex align-items-center gap-1"><FaChartBar /> <span>{__(`${active_installs} ${active_installs > 0 ? "+" : ""} active installations`, "plugin-starter-pro")}</span></div>
                <div className="d-flex align-items-center gap-1"><FaWordpress /><span>{__(`Tested with ${tested}`, "plugin-starter-pro")}</span></div>
            </div>

        </div>
    )
}