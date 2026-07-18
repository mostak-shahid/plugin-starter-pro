import { __ } from "@wordpress/i18n";
import { useCallback, useEffect, useState } from '@wordpress/element';
export default function PluginCard(plugin) {
    const {image, name, short_description, author, plugin_source='internal', plugin_slug='', plugin_file='', download_url='', version='1.0.0', rating='0', num_ratings='0', active_installs='0', tested} = plugin;
    /*
    data-sub_action="install_activate" 
    data-plugin_source="external" 
    data-download_url="https://github.com/mostak-shahid/mos-woocommerce-protected-categories/archive/refs/heads/main.zip"
    data-plugin_slug="mos-woocommerce-protected-categories-main" 
    data-plugin_file="mos-woocommerce-protected-categories.php" 

    data-sub_action="install_activate"  
    data-plugin_source="internal" 
    data-plugin_slug="mos-product-specifications-tab"
    */
    const [pluginStatus, setPluginStatus] = useState("checking");
    const [errorMessage, setErrorMessage] = useState("");

    // Check plugin status on component mount
    const checkPluginStatus = useCallback(async () => {
        setPluginStatus("checking");
        setErrorMessage("");
        try {
            const result = await formDataPost('plugin_starter_ajax_plugins_status', {
                file:plugin_file,
            });
            // console.log("Result:", result); // check structure here
            setPluginStatus(result?.data?.success_message); // Fix this line based on actual response
        } catch (error) {
            setErrorMessage(error.message);
        } finally {
            // setPluginStatusLoading(false);
        }
    }, [plugin_file]);

    useEffect(() => {
        checkPluginStatus();
    }, [checkPluginStatus, plugin_slug]);

    const handlePlugin = async () => {              
        // setProcessing(true);     
        // setActionError(null);   
        // setStatus(status === 'not_active'?'activating':'installing')         
        // try {
        //     const result = await formDataPost('plugin_starter_ajax_install_plugins', {
        //         sub_action:sub_action,
        //         download_url:download_url,                
        //         plugin_slug:plugin_slug,
        //         plugin_file:plugin_file,
        //         plugin_source:plugin_source,
        //     }); 
        //     console.log("Result:", result); // check structure here
        //     setStatus(result.data)
        // } catch (error) {
        //     setActionError(error.message);
        // } finally {
        //     setProcessing(false);
        //     // setStatus(status === 'activating'?'active':'not_active') 
        // }
    };
    
    
    const getButtonLabel = () => {
        switch (pluginStatus) {
            case "checking":
                return __("Checking...", "plugin-starter");
            case "not_installed":
                return __("Install Now", "plugin-starter");
            case "installed":
                return __("Activate", "plugin-starter");
            case "installing":
                return __("Installing...", "plugin-starter");
            case "installation_complete": // New state
                return __("Installed", "plugin-starter");
            case "activating":
                return __("Activating...", "plugin-starter");
            case "activated":
                return __("Activated", "plugin-starter");
            case "error":
                return __("Try Again", "plugin-starter");
            default:
                return __("Install Now", "plugin-starter");
        }
    };
    const handleButtonClick = () => {
		switch (pluginStatus) {
			case "not_installed":
				installPlugin();
				break;
			case "installed":
				activatePlugin();
				break;
			case "error":
				checkPluginStatus();
				break;
			default:
				break;
		}
	};       
    
    const installPlugin = async () => {
        setPluginStatus("installing");
        setErrorMessage("");
        try {
            await formDataPost('plugin_starter_ajax_install_plugins', {
                sub_action:'install',
                download_url:download_url,                
                plugin_slug:plugin_slug,
                plugin_file:plugin_file,
                plugin_source:plugin_source,
            }); 
        } catch (error) {
            setErrorMessage(error.message);
        } finally {
            setPluginStatus("installed"); 
        }
    };

    const activatePlugin = async () => {
        setPluginStatus("activating");
        setErrorMessage("");        
        try {
            await formDataPost('plugin_starter_ajax_install_plugins', {
                sub_action:'activate',
                download_url:download_url,                
                plugin_slug:plugin_slug,
                plugin_file:plugin_file,
                plugin_source:plugin_source,
            }); 
        } catch (error) {
            setErrorMessage(error.message);
        } finally {
            setPluginStatus("activated"); 
        }
    };
    const isButtonDisabled = ["checking", "installing", "activating","installation_complete"].includes(
		pluginStatus,
	);
    return (
        <div className="plugin-starter-plugin-card p-4 border rounded-4">
            <div className="d-flex align-items-center gap-2">
                <img
                    alt={name}
                    src={image}
                    style={{flex: '0 0 80px', maxWidth: '80px'}} 
                />
                <div>
                    <a href={`https://wordpress.org/plugins/${plugin_slug}/`} target="_blank"><h6 className="h6" style={{fontSize: 18, marginBottom: 0}} >{name}</h6></a>
                    <div className="d-flex">
                        {/* <Rating allowHalf defaultValue={(rating/20).toFixed(2)} disabled/>    */}
                        <span>({num_ratings})</span>
                    </div> 
                </div>
            </div>
            {/* <p ellipsis={{ showTooltip: true }} style={{ maxWidth: 250 }}>{short_description}</p> */}
            <div className="mt-3">
                <p>{short_description}</p>
            </div>
            <div className="d-flex justify-content-between mt-2">
                <span dangerouslySetInnerHTML={{__html: author}}/>
                <div className="tag">{version}</div>
            </div>
            <div className="d-flex justify-content-between mt-1">
                <div className="d-flex">IconHistogram <span>{__(`${active_installs} ${active_installs>0?"+":""} active installations`, "plugin-starter")}</span></div>
                <div className="d-flex">WordPress Logo {__(`Tested with ${tested}`, "plugin-starter")}</div>
            </div>
            
        </div>
    )
}