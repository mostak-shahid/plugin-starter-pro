import React from 'react';
import { useState } from '@wordpress/element';


import { __ } from "@wordpress/i18n";
import apiFetch from "@wordpress/api-fetch";

export default function NewsSideSheet({newsVisible, handleNewsVisible}) {
    const [newsItems, setNewsItems] = useState([]);
    const [modalVisible, setModalVisible] = useState(false);
    const [activeNews, setActiveNews] = useState(null);
    const [readNewsIds, setReadNewsIds] = useState([]);
    const itemsPerPage = 5;
    return (
        <>
            Working...
        </>
    )
}
