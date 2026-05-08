import React, { useState, useEffect, Suspense  } from 'react';
export default function LoginForm({settingData}) {
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');

    return (
        <div style={{ padding: 20, border: '1px solid #ddd' }}>
            {/* {console.log('settingData in LoginForm', settingData)} */}
            <div dangerouslySetInnerHTML={{__html:settingData?.tools?.from_pro}}></div>
            <input type="text" placeholder="Email" />
            <br /><br />
            <input type="password" placeholder="Password" />
        </div>
    );
}
