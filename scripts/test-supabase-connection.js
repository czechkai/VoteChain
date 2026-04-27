import "dotenv/config";

const supabaseUrl = process.env.SUPABASE_URL;
const supabaseAnonKey = process.env.SUPABASE_ANON_KEY;

if (!supabaseUrl || !supabaseAnonKey) {
  console.error("Missing SUPABASE_URL or SUPABASE_ANON_KEY in .env");
  process.exit(1);
}

const response = await fetch(`${supabaseUrl}/rest/v1/`, {
  method: "GET",
  headers: {
    apikey: supabaseAnonKey,
    Authorization: `Bearer ${supabaseAnonKey}`
  }
});

if (response.ok || response.status === 401) {
  console.log("Supabase endpoint is reachable.");
  process.exit(0);
}

console.error(`Supabase connection check failed. HTTP ${response.status}`);
process.exit(1);