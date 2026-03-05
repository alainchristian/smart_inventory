const puppeteer = require('puppeteer');
const fs = require('fs');

const OUT_DIR = 'C:/Users/Christian/.gemini/antigravity/brain/47698b0b-11d0-44e4-8d7e-f3e169d25b2f/';

(async () => {
    console.log("Launching browser...");
    const browser = await puppeteer.launch({ headless: 'new' });
    const page = await browser.newPage();

    // Helper function to login
    const login = async (email, password) => {
        console.log(`Logging in as ${email}...`);
        await page.goto('http://localhost:8000/login');
        await page.type('input[type="email"]', email);
        await page.type('input[type="password"]', password);
        await page.click('button[type="submit"]');
        await page.waitForNavigation();
    };

    const logout = async () => {
        console.log("Logging out...");
        // Clear cookies to logout
        const client = await page.target().createCDPSession();
        await client.send('Network.clearBrowserCookies');
    };

    // Helper function to screenshot
    const takeScreenshot = async (url, width, filename) => {
        console.log(`Taking screenshot of ${url} at ${width}px...`);
        await page.setViewport({ width: width, height: 900 });
        await page.goto(url, { waitUntil: 'networkidle0' });
        await page.screenshot({ path: OUT_DIR + filename, fullPage: true });
    };

    try {
        // ----- SHOP 1 SCREENS (Desktop & Mobile) -----
        await login('shop1@kigalifootwear.rw', 'password');

        // Shop Transfers List
        await takeScreenshot('http://localhost:8000/shop/transfers', 1280, 'desktop-shop-transfers.png');
        await takeScreenshot('http://localhost:8000/shop/transfers', 768, 'tablet-shop-transfers.png');
        await takeScreenshot('http://localhost:8000/shop/transfers', 375, 'mobile-shop-transfers.png');

        // Shop Transfer Request Form
        await takeScreenshot('http://localhost:8000/shop/transfers/request', 1280, 'desktop-request-form.png');
        await takeScreenshot('http://localhost:8000/shop/transfers/request', 768, 'tablet-request-form.png');
        await takeScreenshot('http://localhost:8000/shop/transfers/request', 375, 'mobile-request-form.png');

        await logout();

        // ----- WAREHOUSE MANAGER SCREENS (Desktop & Mobile) -----
        await login('wm1@kigalifootwear.rw', 'password');

        // Warehouse Transfers List
        await takeScreenshot('http://localhost:8000/warehouse/transfers', 1280, 'desktop-warehouse-transfers.png');
        await takeScreenshot('http://localhost:8000/warehouse/transfers', 768, 'tablet-warehouse-transfers.png');
        await takeScreenshot('http://localhost:8000/warehouse/transfers', 375, 'mobile-warehouse-transfers.png');

        console.log("All screenshots captured successfully!");
    } catch (e) {
        console.error("Error capturing screenshots:", e);
    } finally {
        await browser.close();
    }
})();
