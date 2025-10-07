# JavaScript Modular Architecture Diagram

## 🏗️ Architecture Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                        APPLICATION LAYER                        │
├─────────────────────────────────────────────────────────────────┤
│  main.js (Entry Point)                                         │
│  ├── Initialize all modules                                    │
│  ├── Set up event listeners                                    │
│  └── Handle application lifecycle                              │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                         CORE MODULES                           │
├─────────────────────────────────────────────────────────────────┤
│  StateManager.js          EventManager.js                      │
│  ├── Centralized state    ├── Event delegation                │
│  ├── Reactive updates     ├── Custom events                   │
│  ├── State validation     ├── Error handling                  │
│  └── State persistence    └── Event cleanup                   │
│                                                               │
│  DataManager.js           ValidationManager.js                │
│  ├── API calls            ├── Form validation                 │
│  ├── Data persistence     ├── Input sanitization             │
│  ├── Error handling       ├── Error reporting                 │
│  └── Caching              └── Validation rules                │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                          UI MODULES                            │
├─────────────────────────────────────────────────────────────────┤
│  TabManager.js            PreviewManager.js                    │
│  ├── Tab switching        ├── Character preview               │
│  ├── Progress tracking    ├── Real-time updates               │
│  ├── State preservation   ├── DOM caching                     │
│  └── Navigation logic     └── Performance optimization        │
│                                                               │
│  UIManager.js             NotificationManager.js              │
│  ├── DOM utilities        ├── User feedback                  │
│  ├── Element caching      ├── Error messages                 │
│  ├── Batch updates        ├── Success notifications          │
│  └── Performance utils    └── Loading states                 │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                        SYSTEM MODULES                          │
├─────────────────────────────────────────────────────────────────┤
│  TraitSystem.js           AbilitySystem.js                    │
│  ├── Trait selection      ├── Ability selection               │
│  ├── Category management  ├── Specialization handling         │
│  ├── XP calculation       ├── XP tracking                     │
│  └── Validation           └── Category validation             │
│                                                               │
│  DisciplineSystem.js      MeritsFlawsSystem.js                │
│  ├── Discipline selection ├── Merit/Flaw selection            │
│  ├── Power management     ├── Conflict checking               │
│  ├── Clan restrictions    ├── Cost calculation                │
│  └── Prerequisites        └── Filtering & sorting             │
│                                                               │
│  BackgroundsSystem.js     MoralitySystem.js                   │
│  ├── Background selection ├── Virtue allocation               │
│  ├── Level management     ├── Humanity calculation            │
│  ├── Generation calc      ├── Moral state display             │
│  └── XP tracking          └── Virtue validation               │
│                                                               │
│  XPSystem.js                                                   │
│  ├── XP tracking                                              │
│  ├── Cost calculation                                         │
│  ├── Validation                                               │
│  └── Display updates                                          │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                        UTILITY MODULES                         │
├─────────────────────────────────────────────────────────────────┤
│  DOMUtils.js              ValidationUtils.js                  │
│  ├── Element queries      ├── Input validation                │
│  ├── Event handling       ├── Data sanitization               │
│  ├── DOM manipulation     ├── Error formatting                │
│  └── Performance cache    └── Validation rules                │
│                                                               │
│  DataUtils.js             PerformanceUtils.js                 │
│  ├── Data transformation  ├── Debouncing                      │
│  ├── Format conversion    ├── Throttling                      │
│  ├── Data validation      ├── Lazy loading                    │
│  └── Serialization        └── Memory management               │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                       CONFIGURATION MODULES                    │
├─────────────────────────────────────────────────────────────────┤
│  constants.js             apiConfig.js                        │
│  ├── Application constants ├── API endpoints                   │
│  ├── Default values        ├── Request configuration          │
│  ├── UI constants          ├── Error handling                 │
│  └── Validation rules      └── Timeout settings               │
└─────────────────────────────────────────────────────────────────┘
```

## 🔄 Data Flow

```
User Input → EventManager → StateManager → System Modules → UI Updates
     ↑                                                           │
     └─────────────────── User Feedback ← NotificationManager ←─┘
```

## 📊 Module Dependencies

```
StateManager (Core)
    ↑
EventManager (Core)
    ↑
TabManager, PreviewManager, UIManager (UI)
    ↑
TraitSystem, AbilitySystem, DisciplineSystem (Systems)
    ↑
DOMUtils, ValidationUtils, DataUtils (Utils)
```

## ⚡ Performance Optimizations

1. **Event Delegation**: Single listeners for multiple elements
2. **DOM Caching**: Cached element references
3. **Debouncing**: Delayed execution for expensive operations
4. **Batch Updates**: Grouped DOM modifications
5. **Lazy Loading**: On-demand module loading
6. **Memory Management**: Proper cleanup and garbage collection

## 🛡️ Error Handling

1. **Module-Level**: Try/catch in each module
2. **Global Handler**: Centralized error management
3. **User Feedback**: Friendly error messages
4. **Logging**: Comprehensive error logging
5. **Recovery**: Graceful degradation

## 📈 Success Metrics

- **Performance**: 50% reduction in DOM queries
- **Maintainability**: 80% reduction in code duplication
- **Reliability**: 100% error handling coverage
- **Modularity**: 9 focused modules vs 1 monolithic file
- **Testability**: Unit tests for each module
