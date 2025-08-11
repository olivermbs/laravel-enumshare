import { exec } from 'child_process';
import { promisify } from 'util';
import path from 'path';
import fs from 'fs';

const execAsync = promisify(exec);

/**
 * Vite plugin to automatically regenerate Laravel enums during development
 * 
 * @param {object} options
 * @param {string} options.command - Laravel command to run (default: 'php artisan enums:export')
 * @param {string[]} options.watchPaths - Paths to watch for changes (default: auto-detected)
 * @param {string} options.locale - Locale for export
 * @param {boolean} options.runOnBuild - Run on production build (default: true)
 */
export function laravelEnums(options = {}) {
  const {
    command = 'php artisan enums:export',
    watchPaths = null,
    locale = null,
    runOnBuild = true
  } = options;

  let isBuilding = false;
  let lastRun = 0;
  const debounceMs = 1000; // Prevent rapid successive runs

  return {
    name: 'laravel-enums',
    
    async buildStart() {
      if (runOnBuild || !isBuilding) {
        await runEnumExport('Build start');
      }
    },

    async buildEnd() {
      isBuilding = false;
    },

    configureServer(server) {
      isBuilding = false;
      
      // Watch enum-related files
      const pathsToWatch = watchPaths || getDefaultWatchPaths();
      
      pathsToWatch.forEach(watchPath => {
        if (fs.existsSync(watchPath)) {
          server.watcher.add(watchPath);
        }
      });

      // Handle file changes
      server.watcher.on('change', async (file) => {
        if (shouldTriggerRegeneration(file)) {
          await runEnumExport(`File changed: ${file}`);
          
          // Trigger HMR for enum files
          const enumFiles = getGeneratedEnumFiles();
          enumFiles.forEach(enumFile => {
            if (fs.existsSync(enumFile)) {
              const module = server.moduleGraph.getModuleById(enumFile);
              if (module) {
                server.reloadModule(module);
              }
            }
          });
        }
      });

      server.watcher.on('add', async (file) => {
        if (shouldTriggerRegeneration(file)) {
          await runEnumExport(`File added: ${file}`);
        }
      });

      server.watcher.on('unlink', async (file) => {
        if (shouldTriggerRegeneration(file)) {
          await runEnumExport(`File removed: ${file}`);
        }
      });
    },
  };

  async function runEnumExport(reason) {
    const now = Date.now();
    if (now - lastRun < debounceMs) {
      return; // Debounce
    }
    lastRun = now;

    try {
      console.log(`🔄 [Laravel Enums] ${reason}`);
      
      let fullCommand = command;
      if (locale) {
        fullCommand += ` --locale=${locale}`;
      }

      const { stdout, stderr } = await execAsync(fullCommand);
      
      if (stdout) {
        console.log(`✅ [Laravel Enums] ${stdout.trim()}`);
      }
      if (stderr) {
        console.warn(`⚠️ [Laravel Enums] ${stderr.trim()}`);
      }
    } catch (error) {
      console.error(`❌ [Laravel Enums] Failed to regenerate:`, error.message);
    }
  }

  function shouldTriggerRegeneration(file) {
    const filePath = path.resolve(file);
    
    // Check if it's a PHP enum file
    if (filePath.endsWith('.php') && file.includes('Enum')) {
      return true;
    }
    
    // Check if it's a translation file
    if (filePath.includes('/lang/') && filePath.endsWith('.php')) {
      return true;
    }
    
    // Check if it's in configured enum paths
    const configPath = path.resolve('config/enumshare.php');
    if (filePath === configPath) {
      return true;
    }
    
    return false;
  }

  function getDefaultWatchPaths() {
    const paths = [];
    
    // Common enum locations
    const commonPaths = [
      'app/Enums',
      'app/Domain/*/Enums',
      'app/Modules/*/Enums',
      'lang',
      'config/enumshare.php'
    ];
    
    commonPaths.forEach(pattern => {
      const fullPath = path.resolve(pattern);
      if (fs.existsSync(fullPath)) {
        paths.push(fullPath);
      }
    });
    
    return paths;
  }

  function getGeneratedEnumFiles() {
    const enumsDir = path.resolve('resources/js/enums');
    const files = [];
    
    if (fs.existsSync(enumsDir)) {
      const entries = fs.readdirSync(enumsDir);
      entries.forEach(entry => {
        const fullPath = path.join(enumsDir, entry);
        if (entry.endsWith('.ts') && entry !== 'EnumRuntime.ts') {
          files.push(fullPath);
        }
      });
    }
    
    return files;
  }
}