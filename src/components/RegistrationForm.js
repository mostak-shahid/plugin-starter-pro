import React from "react";

export default function RegistrationForm({settingData}) {
    return (
        <div style={{ padding: 20, border: '1px solid #ddd' }}>
            {/* {console.log('settingData in RegistrationForm', settingData)} */}
            <div dangerouslySetInnerHTML={{__html:settingData?.tools?.from_pro}}></div>
            <input type="text" placeholder="Email" />
            <br /><br />
            <input type="text" placeholder="Username" />
            <br /><br />
            <input type="password" placeholder="Password" />
        </div>
    );
}
