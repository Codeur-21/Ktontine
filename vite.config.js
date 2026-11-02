import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import { bunny } from "laravel-vite-plugin/fonts";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js", "resources/css/filament/workspace/theme.css"],
            refresh: true,
            fonts: [
                bunny("Plus Jakarta Sans", {
                    alias: "panel",
                    weights: [400, 500, 600, 700],
                    subsets: ["latin", "latin-ext"],
                    display: "swap",
                }),
                bunny("Instrument Sans", {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ["**/storage/framework/views/**"],
        },
    },
});
