import './App.css'
import "@calcom/atoms/globals.min.css";
import {AvailabilitySettings, CalProvider} from "@calcom/atoms";

function Home() {
  return (
      <CalProvider
          clientId={''}
          options={{
            apiUrl: import.meta.env.CAL_API_URL ?? "",
            refreshUrl: ""
          }}
          >
          <AvailabilitySettings />
      </CalProvider>
  );
}

export default Home
