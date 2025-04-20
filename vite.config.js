// import { defineConfig } from "vite";
// import laravel, { refreshPaths } from "laravel-vite-plugin";

// export default defineConfig({
//     plugins: [
//         laravel({
//             input: ["resources/css/app.css", "resources/js/app.js", "resources/css/filament/admin/theme.css" ],
//             refresh: [
//                 ...refreshPaths,
//                 "app/Livewire/**",
//                 "app/Filament/**",
//                 "app/Providers/**",
//             ],
//         }),
//     ],
// });



import { defineConfig } from "vite";
import laravel, { refreshPaths } from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.css", 
                "resources/js/app.js", 
                "resources/css/filament/admin/theme.css"
            ],
            refresh: [
                ...refreshPaths,
                "app/Livewire/**",
                "app/Filament/**",
                "app/Providers/**",
            ],
        }),
    ],
    // Docker-specific configuration
    server: {
        host: "0.0.0.0", // Allow connections from outside the container
        port: 5173,       // Match VITE_PORT in .env
        hmr: {
            host: "localhost", // Required for HMR to work with Nginx
            protocol: "ws",     // WebSocket protocol
        },
    },
});