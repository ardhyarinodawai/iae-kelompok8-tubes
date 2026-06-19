// @lovable.dev/vite-tanstack-config already includes the following — do NOT add them manually
// or the app will break with duplicate plugins:
//   - tanstackStart, viteReact, tailwindcss, tsConfigPaths, nitro (build-only using cloudflare as a default target),
//   componentTagger (dev-only), VITE_* env injection, @ path alias, React/TanStack dedupe,
//   error logger plugins, and sandbox detection (port/host/strictPort).
// You can pass additional config via defineConfig({ vite: { ... }, etc... }) if needed.
import { defineConfig } from "@lovable.dev/vite-tanstack-config";

export default defineConfig({
  tanstackStart: {
    // Redirect TanStack Start's bundled server entry to src/server.ts (our SSR error wrapper).
    // nitro/vite builds from this
    server: { entry: "server" },
  },

  // Override preset Nitro: default-nya "cloudflare", kita ganti ke "node-server"
  // supaya hasil build bisa dijalankan dengan `node .output/server/index.mjs`
  // (cocok untuk deployment via Docker/Node, bukan Cloudflare Workers).
  nitro: {
    preset: "node-server",
  },

  // Tambahkan konfigurasi port di dalam objek vite
  vite: {
    server: {
      port: 8003,
      strictPort: true, // Opsional: Memastikan Vite tidak menggunakan port lain jika 8003 sibuk
    },
  },
});