import './App.css'
import "@calcom/atoms/globals.min.css";
import {AvailabilitySettings, CalProvider} from "@calcom/atoms";
import {useEffect, useState} from "react";

function Home() {
    const [data, setData] = useState("");

    useEffect(() => {
        fetch('http://localhost:3003/v1/users/37?apiKey=cal_edcfa25c9fae2e8d476cb6c02c4dc265')
            .then(async (res) => {
                const result = await res.json();
                setData(result);
            })
            .catch((error) => {
                console.error('Error fetching data:', error);
            });
    }, []);

    return (
        <CalProvider
            accessToken={'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0eXBlIjoiYWNjZXNzX3Rva2VuIiwiY2xpZW50SWQiOiI4OTA3YTFiMDI3ZDA5YmJlMDc4MDIzNzQ3NWEwMGM3NjlkOGE5MTgyZjEzNjA5M2QxNTQ5NmVmN2RhOTA5YWIiLCJvd25lcklkIjo4NywiaWF0IjoxNzE2NTU3OTc3fQ.ybtEWWCV0EUOFF9kqWRoLB6LPAda7cIsVXps6Apl_Sw'}
            clientId={'8907a1b027d09bbe0780237475a00c769d8a9182f136093d15496ef7da909ab'}
            options={{
                apiUrl: "http://localhost:3004/v2",
                refreshUrl : "http://127.0.0.1:5173/",
            }}
        >
            <AvailabilitySettings />
            <div>
                <h3>Résultat de la requête:</h3>
                <pre>{JSON.stringify(data, null, 2)}</pre>
            </div>
        </CalProvider>
    );
}

export default Home
