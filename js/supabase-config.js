// =====================================================
// BukuKas Universal — Supabase Config
// =====================================================

// Import Supabase SDK dari CDN ESM
import { createClient } from 'https://cdn.jsdelivr.net/npm/@supabase/supabase-js/+esm';

// TODO: Ganti dengan URL dan Anon Key dari Project Settings Supabase Anda
const supabaseUrl = 'https://YOUR_PROJECT_ID.supabase.co';
const supabaseKey = 'YOUR_SUPABASE_ANON_KEY';

export const supabase = createClient(supabaseUrl, supabaseKey);
