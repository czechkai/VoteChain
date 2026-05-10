import "dotenv/config";
import { createClient } from "@supabase/supabase-js";

const supabaseUrl = process.env.SUPABASE_URL;
const supabaseAnonKey = process.env.SUPABASE_ANON_KEY;

if (!supabaseUrl || !supabaseAnonKey) {
  console.error("Missing SUPABASE_URL or SUPABASE_ANON_KEY in .env");
  process.exit(1);
}

const supabase = createClient(supabaseUrl, supabaseAnonKey, {
  auth: {
    autoRefreshToken: false,
    persistSession: false,
  },
});

try {
  const before = await supabase
    .from("candidates")
    .select("id, filing_status")
    .is("filing_status", null);

  if (before.error) {
    throw before.error;
  }

  const nullCount = before.data?.length ?? 0;
  console.log(`Found ${nullCount} candidates with NULL filing_status.`);

  if (nullCount > 0) {
    const update = await supabase
      .from("candidates")
      .update({ filing_status: "approved" })
      .is("filing_status", null)
      .select("id, filing_status");

    if (update.error) {
      throw update.error;
    }

    console.log(
      `Updated ${update.data?.length ?? 0} candidate(s) to approved.`,
    );
  } else {
    console.log("No NULL filing_status values found; nothing to update.");
  }

  const after = await supabase
    .from("candidates")
    .select("id, filing_status")
    .order("created_at", { ascending: true });

  if (after.error) {
    throw after.error;
  }

  console.log("Current candidate filing_status values:");
  for (const candidate of after.data ?? []) {
    console.log(`- ${candidate.id}: ${candidate.filing_status}`);
  }

  process.exit(0);
} catch (error) {
  console.error("Update failed:", error.message ?? error);
  process.exit(1);
}
