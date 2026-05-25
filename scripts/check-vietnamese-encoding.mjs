import fs from 'node:fs';
import path from 'node:path';

const roots = ['resources/js', 'resources/views', 'app', 'database', 'docs'];
const mojibakePattern = /Ã|Â|Ä|á»|áº|Æ|â€|â€™|â€œ|â€�/u;
const sourceExtensions = new Set([
  '.js',
  '.mjs',
  '.vue',
  '.php',
  '.blade.php',
  '.md',
]);

const allowedDocumentationExamples = new Set([
  'docs/coding-rules.md',
]);

function isSourceFile(filePath) {
  return [...sourceExtensions].some((extension) => filePath.endsWith(extension));
}

function walk(directory) {
  if (!fs.existsSync(directory)) {
    return [];
  }

  return fs.readdirSync(directory, { withFileTypes: true }).flatMap((entry) => {
    const fullPath = path.join(directory, entry.name);

    if (entry.isDirectory()) {
      return walk(fullPath);
    }

    return isSourceFile(fullPath) ? [fullPath] : [];
  });
}

function shouldIgnoreMatch(filePath, line) {
  const normalizedPath = filePath.replaceAll(path.sep, '/');

  return (
    allowedDocumentationExamples.has(normalizedPath) &&
    line.includes('If Vietnamese appears as')
  );
}

const findings = [];

for (const root of roots) {
  for (const filePath of walk(root)) {
    const content = fs.readFileSync(filePath, 'utf8');

    if (content.charCodeAt(0) === 0xfeff) {
      findings.push(`${filePath}:1: UTF-8 BOM detected`);
    }

    content.split(/\r?\n/).forEach((line, index) => {
      if (mojibakePattern.test(line) && !shouldIgnoreMatch(filePath, line)) {
        findings.push(`${filePath}:${index + 1}: ${line.trim()}`);
      }
    });
  }
}

if (findings.length > 0) {
  console.error('Vietnamese mojibake/encoding issues found:');
  findings.forEach((finding) => console.error(`- ${finding}`));
  process.exit(1);
}

console.log('Vietnamese encoding scan passed.');
