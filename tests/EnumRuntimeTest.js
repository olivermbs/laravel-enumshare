/**
 * Test file for EnumRuntime functionality
 * Run this in a browser console or Node.js environment
 */

// Mock enum data (same structure as FlightBriefingStatus)
const testEnumData = {
    "name": "TestEnum",
    "fqcn": "App\\Enums\\TestEnum",
    "backingType": "int",
    "entries": [
        {
            "key": "DRAFT",
            "value": 0,
            "label": "Draft",
            "meta": {
                "color": "orange"
            }
        },
        {
            "key": "SAVED",
            "value": 1,
            "label": "Saved",
            "meta": {
                "color": "blue"
            }
        },
        {
            "key": "SENT",
            "value": 2,
            "label": "Sent",
            "meta": {
                "color": "green"
            }
        }
    ],
    "options": [
        {
            "value": 0,
            "label": "Draft"
        },
        {
            "value": 1,
            "label": "Saved"
        },
        {
            "value": 2,
            "label": "Sent"
        }
    ]
};

// Import the createEnumProxy function (adjust path as needed)
// import { createEnumProxy } from './EnumRuntime';

// For testing purposes, here's a simplified version of the function
function createEnumProxy(enumData) {
    const entriesMap = {};
    
    // Build entries map
    for (const entry of enumData.entries) {
        entriesMap[entry.key] = entry;
    }
    
    // Base object with metadata and methods
    const baseObject = {
        name: enumData.name,
        entries: enumData.entries,
        options: enumData.options,
        keys: () => enumData.entries.map(entry => entry.key),
        values: () => enumData.entries.map(entry => entry.value ?? entry.key),
        labels: () => enumData.entries.map(entry => entry.label),
    };
    
    // Add the methods explicitly to avoid conflicts
    const findEntryByValue = (value) => {
        const entry = enumData.entries.find(entry => 
            (entry.value !== null ? entry.value : entry.key) === value
        );
        return entry || null;
    };
    
    baseObject.from = findEntryByValue;
    baseObject.fromValue = findEntryByValue;
    
    // Create proxy to handle dynamic property access
    return new Proxy(baseObject, {
        get(target, prop) {
            if (typeof prop === 'string' && entriesMap[prop]) {
                return entriesMap[prop];
            }
            return target[prop];
        },
        
        has(target, prop) {
            if (typeof prop === 'string' && entriesMap[prop]) {
                return true;
            }
            return prop in target;
        },
        
        ownKeys(target) {
            return [...Object.keys(target), ...Object.keys(entriesMap)];
        },
        
        getOwnPropertyDescriptor(target, prop) {
            if (typeof prop === 'string' && entriesMap[prop]) {
                return {
                    enumerable: true,
                    configurable: true,
                    value: entriesMap[prop],
                };
            }
            return Object.getOwnPropertyDescriptor(target, prop);
        },
    });
}

// Test suite
function runTests() {
    console.log('=== EnumRuntime Tests ===');
    
    const testEnum = createEnumProxy(testEnumData);
    
    // Test 1: Basic properties
    console.log('\nTest 1: Basic properties');
    console.log('✓ Name:', testEnum.name === 'TestEnum' ? 'PASS' : 'FAIL');
    console.log('✓ Entries length:', testEnum.entries.length === 3 ? 'PASS' : 'FAIL');
    console.log('✓ Options length:', testEnum.options.length === 3 ? 'PASS' : 'FAIL');
    
    // Test 2: Method existence
    console.log('\nTest 2: Method existence');
    console.log('✓ keys method:', typeof testEnum.keys === 'function' ? 'PASS' : 'FAIL');
    console.log('✓ values method:', typeof testEnum.values === 'function' ? 'PASS' : 'FAIL');
    console.log('✓ labels method:', typeof testEnum.labels === 'function' ? 'PASS' : 'FAIL');
    console.log('✓ from method:', typeof testEnum.from === 'function' ? 'PASS' : 'FAIL');
    console.log('✓ fromValue method:', typeof testEnum.fromValue === 'function' ? 'PASS' : 'FAIL');
    
    // Test 3: Method functionality
    console.log('\nTest 3: Method functionality');
    console.log('✓ keys():', JSON.stringify(testEnum.keys()) === JSON.stringify(['DRAFT', 'SAVED', 'SENT']) ? 'PASS' : 'FAIL');
    console.log('✓ values():', JSON.stringify(testEnum.values()) === JSON.stringify([0, 1, 2]) ? 'PASS' : 'FAIL');
    console.log('✓ labels():', JSON.stringify(testEnum.labels()) === JSON.stringify(['Draft', 'Saved', 'Sent']) ? 'PASS' : 'FAIL');
    
    // Test 4: from method
    console.log('\nTest 4: from method');
    const fromTest1 = testEnum.from(1);
    console.log('✓ from(1) returns object:', fromTest1 !== null ? 'PASS' : 'FAIL');
    console.log('✓ from(1).label:', fromTest1 && fromTest1.label === 'Saved' ? 'PASS' : 'FAIL');
    console.log('✓ from(999) returns null:', testEnum.from(999) === null ? 'PASS' : 'FAIL');
    
    // Test 5: fromValue method
    console.log('\nTest 5: fromValue method');
    const fromValueTest1 = testEnum.fromValue(2);
    console.log('✓ fromValue(2) returns object:', fromValueTest1 !== null ? 'PASS' : 'FAIL');
    console.log('✓ fromValue(2).label:', fromValueTest1 && fromValueTest1.label === 'Sent' ? 'PASS' : 'FAIL');
    console.log('✓ fromValue(999) returns null:', testEnum.fromValue(999) === null ? 'PASS' : 'FAIL');
    
    // Test 6: Enum entries access
    console.log('\nTest 6: Enum entries access');
    console.log('✓ DRAFT exists:', testEnum.DRAFT !== undefined ? 'PASS' : 'FAIL');
    console.log('✓ DRAFT.label:', testEnum.DRAFT && testEnum.DRAFT.label === 'Draft' ? 'PASS' : 'FAIL');
    console.log('✓ SAVED.value:', testEnum.SAVED && testEnum.SAVED.value === 1 ? 'PASS' : 'FAIL');
    
    // Test 7: Property enumeration
    console.log('\nTest 7: Property enumeration');
    const ownKeys = Object.getOwnPropertyNames(testEnum);
    console.log('✓ Has from in keys:', ownKeys.includes('from') ? 'PASS' : 'FAIL');
    console.log('✓ Has fromValue in keys:', ownKeys.includes('fromValue') ? 'PASS' : 'FAIL');
    console.log('✓ Has DRAFT in keys:', ownKeys.includes('DRAFT') ? 'PASS' : 'FAIL');
    
    // Test 8: hasOwnProperty
    console.log('\nTest 8: hasOwnProperty');
    console.log('✓ hasOwnProperty(from):', testEnum.hasOwnProperty('from') ? 'PASS' : 'FAIL');
    console.log('✓ hasOwnProperty(fromValue):', testEnum.hasOwnProperty('fromValue') ? 'PASS' : 'FAIL');
    console.log('✓ hasOwnProperty(DRAFT):', testEnum.hasOwnProperty('DRAFT') ? 'PASS' : 'FAIL');
    
    console.log('\n=== Tests Complete ===');
    
    // Return the enum for manual inspection
    return testEnum;
}

// Run the tests
const testResult = runTests();

// Export for browser console testing
if (typeof window !== 'undefined') {
    window.testEnum = testResult;
    console.log('\nTest enum available as window.testEnum for manual testing');
}