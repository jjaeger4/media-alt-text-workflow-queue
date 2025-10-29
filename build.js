#!/usr/bin/env node

/**
 * Build Script for WordPress.org Plugin Submission
 * 
 * Creates a clean ZIP file with only the files needed for WordPress.org
 * Excludes development files, documentation, and Git-related files
 */

const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

// Configuration
const PLUGIN_SLUG = 'media-alt-text-workflow-queue';
const VERSION = '1.0.0'; // Update this for each release
const OUTPUT_DIR = 'dist';
const OUTPUT_ZIP = `${PLUGIN_SLUG}-${VERSION}.zip`;

// Files and directories to include in the WordPress.org ZIP
const INCLUDE = [
    'src',
    'assets',
    'LICENSE.txt',
    'readme.txt',
    'uninstall.php',
    'media-alt-text-workflow-queue.php'
];

// Color output
const colors = {
    reset: '\x1b[0m',
    green: '\x1b[32m',
    blue: '\x1b[34m',
    yellow: '\x1b[33m',
    red: '\x1b[31m'
};

function log(message, color = 'reset') {
    console.log(`${colors[color]}${message}${colors.reset}`);
}

function ensureDir(dir) {
    if (!fs.existsSync(dir)) {
        fs.mkdirSync(dir, { recursive: true });
    }
}

function copyRecursive(src, dest) {
    const stats = fs.statSync(src);
    
    if (stats.isDirectory()) {
        const entries = fs.readdirSync(src);
        
        // Only create directory if it has files
        if (entries.length > 0) {
            ensureDir(dest);
            
            for (const entry of entries) {
                copyRecursive(
                    path.join(src, entry),
                    path.join(dest, entry)
                );
            }
        }
    } else {
        // Ensure parent directory exists
        const parentDir = path.dirname(dest);
        ensureDir(parentDir);
        fs.copyFileSync(src, dest);
    }
}

function removeDir(dir) {
    if (fs.existsSync(dir)) {
        fs.rmSync(dir, { recursive: true, force: true });
    }
}

function main() {
    log('========================================', 'blue');
    log('  WordPress.org Plugin Build Script', 'blue');
    log('========================================', 'blue');
    log('');
    
    // Clean up old builds
    log('🧹 Cleaning up old builds...', 'yellow');
    removeDir(OUTPUT_DIR);
    
    // Create output directory (files at root, not in subdirectory)
    log('📁 Creating build directory...', 'yellow');
    const buildDir = OUTPUT_DIR;
    ensureDir(buildDir);
    
    // Copy files
    log('📦 Copying plugin files...', 'yellow');
    let fileCount = 0;
    let totalFiles = 0;
    
    for (const item of INCLUDE) {
        if (fs.existsSync(item)) {
            const stats = fs.statSync(item);
            const dest = path.join(buildDir, item);
            
            if (stats.isDirectory()) {
                // Count files in directory
                const countFiles = (dir) => {
                    let count = 0;
                    const entries = fs.readdirSync(dir);
                    for (const entry of entries) {
                        const fullPath = path.join(dir, entry);
                        const entryStats = fs.statSync(fullPath);
                        if (entryStats.isDirectory()) {
                            count += countFiles(fullPath);
                        } else {
                            count++;
                        }
                    }
                    return count;
                };
                const filesInDir = countFiles(item);
                log(`   ✓ ${item} (${filesInDir} files)`, 'green');
                totalFiles += filesInDir;
            } else {
                log(`   ✓ ${item}`, 'green');
                totalFiles++;
            }
            
            copyRecursive(item, dest);
            fileCount++;
        } else {
            log(`   ⚠ ${item} not found (skipping)`, 'yellow');
        }
    }
    
    log('');
    log(`✅ Copied ${fileCount} items (${totalFiles} total files)`, 'green');
    log('');
    
    // Create ZIP file
    log('📦 Creating ZIP archive...', 'yellow');
    
    try {
        // Check if zip command is available (Unix/Mac/WSL)
        try {
            execSync('which zip', { stdio: 'ignore' });
            execSync(`cd ${OUTPUT_DIR} && zip -r ../${OUTPUT_ZIP} .`, { stdio: 'inherit' });
        } catch {
            // Fall back to PowerShell on Windows
            const psCommand = `Compress-Archive -Path "${OUTPUT_DIR}\\*" -DestinationPath "${OUTPUT_ZIP}" -Force`;
            execSync(`powershell -Command "${psCommand}"`, { stdio: 'inherit' });
        }
        
        log('');
        log('========================================', 'green');
        log('✅ BUILD SUCCESSFUL!', 'green');
        log('========================================', 'green');
        log('');
        log(`📦 Output: ${OUTPUT_ZIP}`, 'blue');
        
        // Get file size
        const stats = fs.statSync(OUTPUT_ZIP);
        const fileSizeInKB = (stats.size / 1024).toFixed(2);
        log(`📊 Size: ${fileSizeInKB} KB`, 'blue');
        
        log('');
        log('Next steps:', 'yellow');
        log('1. Test the ZIP by installing it on a fresh WordPress site', 'reset');
        log('');
        
    } catch (error) {
        log('');
        log('❌ BUILD FAILED!', 'red');
        log(`Error: ${error.message}`, 'red');
        process.exit(1);
    }
    
    // Clean up temp directory (keep ZIP)
    log('🧹 Cleaning up temporary files...', 'yellow');
    removeDir(OUTPUT_DIR);
    log('✅ Done!', 'green');
    log('');
}

// Run the build
main();

