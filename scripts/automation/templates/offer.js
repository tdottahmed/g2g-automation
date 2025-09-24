const offerCards = {
    selector: "form > .row >.col-12:nth-of-type(:NUMBER:) .g-cu-form-card",
    items: [
        {
            name: "Offer details",
            sections: {
                selector: ".g-cu-form-card__section:nth-of-type(:NUMBER:)",
                items: [
                    null, // Skip first section (title/description)
                    {
                        name: "Levels",
                        fields: {
                            selector: "div.q-col-gutter-sm",
                            type: "dropdown",
                            items: [
                                {
                                    label: "Town Hall Level",
                                },
                                {
                                    label: "King Level",
                                },
                                {
                                    label: "Queen Level",
                                },
                                {
                                    label: "Warden Level",
                                },
                                {
                                    label: "Champion Level",
                                },
                            ],
                        },
                    },
                    {
                        name: "Title & Description",
                        fields: {
                            selector: "div.q-col-gutter-sm",
                            type: "dropdown",
                            items: [
                                {
                                    label: "Title",
                                    type: "text",
                                },
                                {
                                    label: "Description",
                                    type: "text",
                                },
                            ],
                        },
                    },
                ],
            },
        },
    ],
};
export default offerCards;
