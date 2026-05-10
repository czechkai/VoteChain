import "dotenv/config";

const supabaseUrl = process.env.SUPABASE_URL;
const supabaseAnonKey = process.env.SUPABASE_ANON_KEY;

if (!supabaseUrl || !supabaseAnonKey) {
  console.error("Missing SUPABASE_URL or SUPABASE_ANON_KEY in .env");
  process.exit(1);
}

try {
  const response = await fetch(`${supabaseUrl}/rest/v1/`, {
    method: "GET",
    headers: {
      apikey: supabaseAnonKey,
      Authorization: `Bearer ${supabaseAnonKey}`
    }
  });

  if (response.ok || response.status === 401) {
    console.log("✅ Supabase endpoint is reachable.");
    setTimeout(() => process.exit(0), 100);
  } else {
    console.error(`❌ Supabase connection check failed. HTTP ${response.status}`);
    setTimeout(() => process.exit(1), 100);
  }
} catch (error) {
  console.error(`❌ Connection error: ${error.message}`);
  setTimeout(() => process.exit(1), 100);
}