/*
 * XGProyect — admin CP entry.
 *
 * Loads:
 *   - Alpine (for dropdowns/modals — anything reactive on the page)
 *   - lucide icons (auto-rendered from `data-lucide="name"` attrs)
 *   - admin shell (sidebar collapse + active link)
 *   - admin tables (progressive enhancement: sort/filter/paginate)
 */

import Alpine from "alpinejs";
import collapse from "@alpinejs/collapse";
import { createIcons, icons } from "lucide";
import { initAdminShell } from "./admin/shell.js";
import { initAdminTables } from "./admin/table.js";

window.Alpine = Alpine;
Alpine.plugin(collapse);

function bootstrap() {
    initAdminShell();
    initAdminTables();
    Alpine.start();

    // Render every <i data-lucide="x"> on the page into an SVG.
    createIcons({ icons });
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", bootstrap);
} else {
    bootstrap();
}
