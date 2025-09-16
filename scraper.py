const axios = require('axios');
const cheerio = require('cheerio');
const fs = require('fs');
const path = require('path');

const URL = "https://www.kv.ee/search?orderby=cdwl&deal_type=1&county=1&parish=1061&city%5B0%5D=5701&city%5B1%5D=1003&city%5B2%5D=1004&city%5B3%5D=1006&city%5B4%5D=1010&rooms_min=1&rooms_max=2&price_max=120000";
const PREVIOUS_LISTINGS_FILE = path.join(__dirname, 'previous_listings.json');
const NEW_LISTINGS_FILE = path.join(__dirname, 'new_listings.json');

async function scrapeWebsite() {
    try {
        const { data } = await axios.get(URL);
        const $ = cheerio.load(data);

        const currentListings = [];
        // You will need to inspect the website's HTML to find the correct selectors.
        // These are examples.
        $('.default-listing-view').each((i, element) => {
            const title = $(element).find('.object-title a').text().trim();
            const link = 'https://www.kv.ee' + $(element).find('.object-title a').attr('href');
            if (title && link) {
                currentListings.push({ title, link });
            }
        });

        // Load previous listings
        let previousListings = [];
        if (fs.existsSync(PREVIOUS_LISTINGS_FILE)) {
            previousListings = JSON.parse(fs.readFileSync(PREVIOUS_LISTINGS_FILE, 'utf-8'));
        }

        // Find new listings by comparing current ones with previous ones
        const new_listings = currentListings.filter(current =>
            !previousListings.some(prev => prev.link === current.link)
        );

        // Save the new listings to a temporary file for the PHP script
        fs.writeFileSync(NEW_LISTINGS_FILE, JSON.stringify(new_listings, null, 2));

        // Save all current listings to update the previous listings file for the next run
        fs.writeFileSync(PREVIOUS_LISTINGS_FILE, JSON.stringify(currentListings, null, 2));

        console.log(`Found ${new_listings.length} new listings.`);

    } catch (error) {
        console.error(`Error during scraping: ${error.message}`);
    }
}

scrapeWebsite();