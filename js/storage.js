const STORAGE_KEYS = {
    USERS: 'samaru_users',
    LOGS: 'samaru_logs',
    BENEFITS: 'samaru_benefits'
};

const ROLES = {
    ADMIN: 1,
    WASTE_COLLECTOR: 2,
    COMMUNITY_MANAGER: 3 // "Community Collector"
};

// Seed Data
const seedData = () => {
    if (!localStorage.getItem(STORAGE_KEYS.USERS)) {
        const users = [
            { id: 1, user_unique_id: 'USR-8821', name: 'John Doe', role_level: ROLES.WASTE_COLLECTOR, phone: '08012345678', address: 'Zone A', created_at: '2023-01-15' },
            { id: 2, user_unique_id: 'USR-9932', name: 'Jane Smith', role_level: ROLES.WASTE_COLLECTOR, phone: '08087654321', address: 'Zone B', created_at: '2023-02-20' },
            { id: 3, user_unique_id: 'ADM-001', name: 'Admin User', role_level: ROLES.ADMIN, phone: '', address: '', created_at: '2023-01-01' },
            { id: 4, user_unique_id: 'CC-101', name: 'Sarah Connor', role_level: ROLES.COMMUNITY_MANAGER, phone: '08055555555', address: 'Zone A Manager', created_at: '2023-03-10' }
        ];
        localStorage.setItem(STORAGE_KEYS.USERS, JSON.stringify(users));
    }
    if (!localStorage.getItem(STORAGE_KEYS.LOGS)) {
        const logs = [
            { id: 1, user_id: 1, user_unique_id: 'USR-8821', action_type: 'received', waste_type: 'organic', weight_kg: 12.5, created_at: '2023-10-25 10:30:00', tx_hash: 'anchored_valid' },
            { id: 2, user_id: 2, user_unique_id: 'USR-9932', action_type: 'received', waste_type: 'recyclable', weight_kg: 8.2, created_at: '2023-10-26 14:15:00', tx_hash: 'anchored_valid' },
            { id: 3, user_id: 1, user_unique_id: 'USR-8821', action_type: 'received', waste_type: 'non_recyclable', weight_kg: 3.0, created_at: '2023-10-27 09:00:00', tx_hash: null }
        ];
        localStorage.setItem(STORAGE_KEYS.LOGS, JSON.stringify(logs));
    }
    if (!localStorage.getItem(STORAGE_KEYS.BENEFITS)) {
        const benefits = [
            { id: 1, user_unique_id: 'CC-101', benefit_type: 'manure_sales', description: 'Sold 50 bags of organic fertilizer', amount_value: '₦ 50,000', created_at: '2023-10-28 10:00:00' },
            { id: 2, user_unique_id: 'CC-101', benefit_type: 'plastic_recycling', description: 'Processed 200kg plastic', amount_value: '₦ 25,000', created_at: '2023-10-29 11:30:00' }
        ];
        localStorage.setItem(STORAGE_KEYS.BENEFITS, JSON.stringify(benefits));
    }
};

seedData();

const Storage = {
    getUsers: () => JSON.parse(localStorage.getItem(STORAGE_KEYS.USERS) || '[]'),
    getLogs: () => JSON.parse(localStorage.getItem(STORAGE_KEYS.LOGS) || '[]'),
    getBenefits: () => JSON.parse(localStorage.getItem(STORAGE_KEYS.BENEFITS) || '[]'),

    // Get users by role
    getCollectors: () => Storage.getUsers().filter(u => u.role_level === ROLES.WASTE_COLLECTOR),
    getManagers: () => Storage.getUsers().filter(u => u.role_level === ROLES.COMMUNITY_MANAGER),

    addUser: (user) => {
        const users = Storage.getUsers();
        user.id = users.length + 1;
        user.created_at = new Date().toISOString().split('T')[0];
        users.push(user);
        localStorage.setItem(STORAGE_KEYS.USERS, JSON.stringify(users));
        return user;
    },

    addLog: (log) => {
        const logs = Storage.getLogs();
        log.id = logs.length + 1;
        log.created_at = new Date().toISOString().replace('T', ' ').substring(0, 19);
        logs.push(log);
        localStorage.setItem(STORAGE_KEYS.LOGS, JSON.stringify(logs));
        return log;
    },

    addBenefit: (benefit) => {
        const benefits = Storage.getBenefits();
        benefit.id = benefits.length + 1;
        benefit.created_at = new Date().toISOString().replace('T', ' ').substring(0, 19);
        benefits.push(benefit);
        localStorage.setItem(STORAGE_KEYS.BENEFITS, JSON.stringify(benefits));
        return benefit;
    },

    getStats: () => {
        const logs = Storage.getLogs();
        const users = Storage.getUsers();

        const totalWaste = logs.reduce((acc, log) => acc + (parseFloat(log.weight_kg) || 0), 0);
        const activeCollectors = users.filter(u => u.role_level === ROLES.WASTE_COLLECTOR).length;
        const totalRecords = logs.length;

        // Composition
        const organic = logs.filter(l => l.waste_type === 'organic').reduce((acc, l) => acc + l.weight_kg, 0);
        const recyclable = logs.filter(l => l.waste_type === 'recyclable').reduce((acc, l) => acc + l.weight_kg, 0);
        const other = logs.filter(l => l.waste_type === 'non_recyclable').reduce((acc, l) => acc + l.weight_kg, 0);

        return {
            totalWaste,
            activeCollectors,
            totalRecords,
            organic,
            recyclable,
            other,
            recentLogs: logs.slice().reverse().slice(0, 5)
        };
    }
};
