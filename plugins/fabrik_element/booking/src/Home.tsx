import './App.css'
import "@calcom/atoms/globals.min.css";
import {AvailabilitySettings, CalProvider, Booker} from "@calcom/atoms";

function Home() {
    return (
        <CalProvider
            accessToken={'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0eXBlIjoiYWNjZXNzX3Rva2VuIiwiY2xpZW50SWQiOiI3Nzc0ZWIyZTVlN2Q3YjQzODdjMmMzZWQzMWM4ODE1MTJkMDdkOTBiZDdmY2VlMjc0ZWM2YTQxMzJmMGMwZTljIiwib3duZXJJZCI6MTI2LCJpYXQiOjE3MTY4OTg1ODF9.poZjUW-3yrWp4SSbO3fLdNziAr6jOap2r4hpFNhSoiA'}
            clientId={'7774eb2e5e7d7b4387c2c3ed31c881512d07d90bd7fcee274ec6a4132f0c0e9c'}
            options={{
                apiUrl: "http://localhost:3004/v2",
                refreshUrl : "http://127.0.0.1:5173/",
            }}
        >
            <AvailabilitySettings />
            <Booker
                username={"final-7774eb2e5e7d7b4387c2c3ed31c881512d07d90bd7fcee274ec6a4132f0c0e9c-example"}
                eventSlug={"thirty-minutes"}
            />

        </CalProvider>
    );
}

export default Home
