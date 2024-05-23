import './App.css'
import "@calcom/atoms/globals.min.css";
import {AvailabilitySettings, CalProvider} from "@calcom/atoms";
import {useEffect, useState} from "react";

function Home() {
    const [data, setData] = useState("");

    useEffect(() => {
        fetch('http://localhost:3003/v1/users/37?apiKey=API_KEY')
            .then(async (res) => {
                const result = await res.json();
                setData(result);
            })
            .catch((error) => {
                console.error('Error fetching data:', error);
            });
    }, []);

    console.log(data);

    return (
        <CalProvider
            clientId={'CLIENT_ID'}
            options={{
                apiUrl: "https://api.cal.com/v2",
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
