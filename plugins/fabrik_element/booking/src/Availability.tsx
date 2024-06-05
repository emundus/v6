import { useState, useEffect } from 'react';
import './App.css';
import "@calcom/atoms/globals.min.css";
import { CalProvider, AvailabilitySettings } from "@calcom/atoms";

interface FetchResponse {
    data: {
        accessToken: string;
    };
    clientData: {
        username: string;
    };
}

function Availability(pageProps: any) {
    const props = pageProps.pageProps;

    const [data, setData] = useState<FetchResponse | null>(null);

    useEffect(() => {
        fetch('http://localhost:3004/v2/oauth-clients/' + import.meta.env.VITE_CLIENT_ID + '/users/' + props.user + '/force-refresh', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'x-cal-secret-key': import.meta.env.VITE_CLIENT_SECRET
            },
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then((data: FetchResponse) => {
                setData(data);
            })
            .catch(error => {
                console.error('There was a problem with the fetch operation:', error);
            });
    }, []);

    return (
        <CalProvider
            accessToken={data?.data.accessToken}
            clientId={'Je_sers_a_quelque_chose_?'}
            options={{
                apiUrl: "http://localhost:3004/v2",
            }}
        >
            <AvailabilitySettings
                customClassNames={{
                    subtitlesClassName: "text-red-500",
                    ctaClassName: "border p-4 rounded-md",
                    containerClassName: "bg-gray-100 p-4",
                }}/>
        </CalProvider>
    );
}

export default Availability;
