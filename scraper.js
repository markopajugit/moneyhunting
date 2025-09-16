const axios = require('axios');
const cheerio = require('cheerio');
const fs = require('fs');
const path = require('path');

const URL = "https://www.kv.ee/search?orderby=cdwl&deal_type=1&county=1&parish=1061&city%5B0%5D=5701&city%5B1%5D=1003&city%5B2%5D=1004&city%5B3%5D=1006&city%5B4%5D=1010&rooms_min=1&rooms_max=2&price_max=120000";
const PREVIOUS_LISTINGS_FILE = path.join(__dirname, 'previous_listings.txt');
const NEW_LISTINGS_FILE = path.join(__dirname, 'new_listings.txt'); // Changed to .txt

async function scrapeWebsite() {
    try {
        const { data } = await axios.get(URL);
        const $ = cheerio.load(data);

        const currentLinks = [];
        // Extract the links of the current listings
        $('.default-listing-view .object-title a').each((i, element) => {
            const link = 'https://www.kv.ee' + $(element).attr('href');
            currentLinks.push(link);
        });

        // Load previous links from the text file
        let previousLinks = [];
        if (fs.existsSync(PREVIOUS_LISTINGS_FILE)) {
            const fileContent = fs.readFileSync(PREVIOUS_LISTINGS_FILE, 'utf-8');
            previousLinks = fileContent.split('\n').filter(Boolean); // Split by line and remove empty lines
        }

        // Find new links
        const newLinks = currentLinks.filter(link => !previousLinks.includes(link));

        // Save the new links to a temporary text file for the PHP script
        fs.writeFileSync(NEW_LISTINGS_FILE, newLinks.join('\n'));

        // Overwrite the previous listings file with all current links for the next run
        fs.writeFileSync(PREVIOUS_LISTINGS_FILE, currentLinks.join('\n'));

        console.log(`Found ${newLinks.length} new listings.`);

    } catch (error) {
        console.error(`Error during scraping: ${error.message}`);
    }
}

scrapeWebsite();