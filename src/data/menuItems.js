// const menuItems = [
//     {
//         itemKey: "pro-dashboard",
//         text: "Pro Dashboard",
//         description: "Exclusive Pro Section",
//         url: "/settings/pro-dashboard",
//     }
// ];

// export default menuItems;
const menuItems = [
    // {
    //     itemKey: "pro-advanced",
    //     text: "Pro Advanced",
    //     url: "/settings/pro-advanced",
    //     insertBefore: "tools", // place before "tools"
    // },

    // {
    //     itemKey: "pro-index-2",
    //     text: "Indexed Example",
    //     url: "/settings/pro-indexed",
    //     position: 2, // insert at index 2
    // },
    // {
    //     "itemKey": "auto-login",
    //     "text": "Auto Login",
    //     "description": "This AuthGuard add-on lets your users generates a unique URL from login page who you don't want to provide a password to login into your site. The generated URL will be sent to the user's email address. This is helpful for those users who don't want to remember their password or you want to provide a temporary access to your site without sharing the password. You can set the expiration time for the generated URL and also limit the number of times it can be used.",
    //     "url": "/settings/auto-login",
    //     parentKey: "user-access", // inside free plugin submenu "page"
    // },
    {
        "itemKey": "more",
        "text": "More",
        "description": "Enable additional features, extensions, and advanced options.",
        "url": "/settings/more",
        // parentKey: "utilities", // inside free plugin submenu "page"
        position: 2, // insert at index 2
    },

    // {
    //     itemKey: "pro-insights",
    //     text: "Insights",
    //     url: "/settings/pro/insights",
    //     parentKey: "page", // inside free plugin submenu "page"
    // },

    // {
    //     itemKey: "pro-dashboard",
    //     text: "Pro Dashboard",
    //     url: "/settings/pro-dashboard",
    //     insertAfter: "more", // after "more"
    // }

    {
        itemKey: "props-passing",
        text: "Props Passing",
        url: "/settings/inputs/props_passing",
        parentKey: "inputs", // after "more"
    },

    {
        itemKey: "bridge",
        text: "Bridge",
        url: "/settings/inputs/bridge",
        parentKey: "inputs", // after "more"
    },
];

export default menuItems;

