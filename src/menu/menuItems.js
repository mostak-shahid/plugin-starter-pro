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
    {
        itemKey: "pro-advanced",
        text: "Pro Advanced",
        url: "/settings/pro-advanced",
        insertBefore: "tools", // place before "tools"
    },

    {
        itemKey: "pro-index-2",
        text: "Indexed Example",
        url: "/settings/pro-indexed",
        position: 2, // insert at index 2
    },

    {
        itemKey: "pro-insights",
        text: "Insights",
        url: "/settings/pro/insights",
        parentKey: "page", // inside free plugin submenu "page"
    },

    {
        itemKey: "pro-dashboard",
        text: "Pro Dashboard",
        url: "/settings/pro-dashboard",
        insertAfter: "more", // after "more"
    }
];

export default menuItems;

