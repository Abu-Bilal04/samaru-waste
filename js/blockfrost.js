const BLOCKFROST_PROJECT_ID = 'preprodvwqnbKpHdgfNRpUgma76NEyvgcVeJLqr'; // Preprod Network
const BLOCKFROST_URL = 'https://cardano-preprod.blockfrost.io/api/v0';

const Blockfrost = {
    headers: {
        'project_id': BLOCKFROST_PROJECT_ID,
        'Content-Type': 'application/json'
    },

    fetchLatestBlock: async () => {
        try {
            const response = await fetch(`${BLOCKFROST_URL}/blocks/latest`, {
                headers: Blockfrost.headers
            });
            if (!response.ok) throw new Error('Network response was not ok');
            return await response.json();
        } catch (error) {
            console.error('Blockfrost Error:', error);
            return null;
        }
    },

    fetchNetworkInfo: async () => {
        try {
            const response = await fetch(`${BLOCKFROST_URL}/network`, {
                headers: Blockfrost.headers
            });
            return await response.json();
        } catch (error) {
            console.error('Blockfrost Network Error:', error);
            return null;
        }
    },

    // Simulate anchoring by getting the latest block hash
    anchorData: async () => {
        const block = await Blockfrost.fetchLatestBlock();
        return block ? block.hash : null;
    }
};
