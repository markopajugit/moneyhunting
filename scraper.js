const axios = require('axios');
const cheerio = require('cheerio');
const fs = require('fs');
const path = require('path');

// Log the start of the Node.js script
console.log(`[${new Date().toISOString()}] Scraper starting...`);

const URL = "https://www.kv.ee/search?orderby=cdwl&deal_type=1&county=1&parish=1061&city%5B0%5D=5701&city%5B1%5D=1003&city%5B2%5D=1004&city%5B3%5D=1006&city%5B4%5D=1010&rooms_min=1&rooms_max=2&price_max=120000";
const PREVIOUS_LISTINGS_FILE = path.join(__dirname, 'previous_listings.json');
const NEW_LISTINGS_FILE = path.join(__dirname, 'new_listings.json');

async function scrapeWebsite() {
    try {
        console.log(`[${new Date().toISOString()}] Fetching URL: ${URL}`);
        const { data } = await axios.get(URL);
        const $ = cheerio.load(data);
        console.log(`[${new Date().toISOString()}] Successfully fetched and parsed HTML.`);

        const currentListings = [];
        $('.default-listing-view').each((i, element) => {
            const title = $(element).find('.object-title a').text().trim();
            const link = 'https://www.kv.ee' + $(element).find('.object-title a').attr('href');
            if (title && link) {
                currentListings.push({ title, link });
            }
        });

        console.log(`[${new Date().toISOString()}] Found ${currentListings.length} total listings on the page.`);

        let previousListings = [];
        if (fs.existsSync(PREVIOUS_LISTINGS_FILE)) {
            try {
                previousListings = JSON.parse(fs.readFileSync(PREVIOUS_LISTINGS_FILE, 'utf-8'));
                console.log(`[${new Date().toISOString()}] Loaded ${previousListings.length} previous listings.`);
            } catch (readError) {
                console.error(`[${new Date().toISOString()}] ERROR: Could not read or parse previous_listings.json: ${readError.message}`);
                // Continue with an empty previous list to prevent script from stopping
            }
        } else {
            console.log(`[${new Date().toISOString()}] Previous listings file not found. Assuming this is the first run.`);
        }

        const new_listings = currentListings.filter(current =>
            !previousListings.some(prev => prev.link === current.link)
        );

        console.log(`[${new Date().toISOString()}] Found ${new_listings.length} new listings.`);

        try {
            fs.writeFileSync(NEW_LISTINGS_FILE, JSON.stringify(new_listings, null, 2));
            console.log(`[${new Date().toISOString()}] Successfully wrote new listings to ${NEW_LISTINGS_FILE}`);

            fs.writeFileSync(PREVIOUS_LISTINGS_FILE, JSON.stringify(currentListings, null, 2));
            console.log(`[${new Date().toISOString()}] Successfully updated previous listings file.`);
        } catch (writeError) {
            console.error(`[${new Date().toISOString()}] ERROR: Could not write files: ${writeError.message}`);
        }

    } catch (error) {
        console.error(`[${new Date().toISOString()}] FATAL ERROR: During scraping: ${error.message}`);
    }
}

scrapeWebsite();