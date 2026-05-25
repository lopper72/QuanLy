import fs from 'node:fs';
import path from 'node:path';

const root = process.cwd();
const scanRoot = path.join(root, 'resources', 'js');
const extensions = new Set(['.vue', '.js']);

const allowedSingleWords = new Set([
  'id',
  'key',
  'type',
  'name',
  'date',
  'score',
  'note',
  'notes',
  'status',
  'level',
  'route',
  'props',
  'value',
  'label',
  'icon',
  'classes',
  'component',
  'template',
  'script',
  'style',
  'slot',
  'default',
  'required',
  'validator',
  'string',
  'number',
  'object',
  'array',
  'boolean',
  'null',
  'true',
  'false',
]);

const knownUiWords = new Set([
  'actions',
  'add',
  'apply',
  'back',
  'cancel',
  'clear',
  'create',
  'custom',
  'daily',
  'delete',
  'details',
  'edit',
  'filter',
  'loading',
  'monthly',
  'next',
  'previous',
  'remove',
  'reset',
  'save',
  'schedule',
  'search',
  'submit',
  'update',
  'view',
  'weekly',
]);

const cssTokens = new Set([
  'absolute', 'active', 'align', 'animate', 'aspect', 'auto', 'backdrop', 'basis', 'bg', 'block',
  'border', 'bottom', 'box', 'break', 'capitalize', 'center', 'col', 'container', 'content', 'cursor',
  'dark', 'decoration', 'delay', 'disabled', 'divide', 'duration', 'ease', 'end', 'fill', 'fixed',
  'flex', 'flow', 'focus', 'font', 'from', 'gap', 'grid', 'group', 'grow', 'h', 'hidden', 'hover',
  'inline', 'inset', 'italic', 'items', 'justify', 'leading', 'left', 'lg', 'line', 'ltr', 'max', 'mb',
  'md', 'min', 'ml', 'mr', 'mt', 'mx', 'my', 'object', 'opacity', 'order', 'origin', 'overflow', 'p', 'pb',
  'pl', 'pointer', 'pr', 'pt', 'px', 'py', 'relative', 'right', 'ring', 'rotate', 'rounded', 'row',
  'scale', 'select', 'self', 'shadow', 'shrink', 'sm', 'space', 'sr', 'start', 'static', 'stroke',
  'rtl', 'table', 'text', 'to', 'top', 'tracking', 'transition', 'translate', 'uppercase', 'via', 'visible',
  'w', 'whitespace', 'xl', 'xs', 'z',
]);

const findings = [];

function walk(dir) {
  const entries = fs.readdirSync(dir, { withFileTypes: true });
  for (const entry of entries) {
    const full = path.join(dir, entry.name);
    if (entry.isDirectory()) {
      walk(full);
      continue;
    }
    if (extensions.has(path.extname(entry.name))) {
      scanFile(full);
    }
  }
}

function stripComments(source) {
  return source
    .replace(/<!--[\s\S]*?-->/g, '')
    .replace(/\/\*[\s\S]*?\*\//g, '')
    .replace(/(^|[^:])\/\/.*$/gm, '$1');
}

function hasVietnamese(value) {
  return /[ÀÁÂÃÈÉÊÌÍÒÓÔÕÙÚĂĐĨŨƠƯàáâãèéêìíòóôõùúăđĩũơưẠ-ỹ]/u.test(value);
}

function isLikelyInternal(value) {
  const trimmed = value.trim();
  if (!trimmed) return true;
  if (hasVietnamese(trimmed)) return true;
  if (/^[@./#]/.test(trimmed)) return true;
  if (/^https?:\/\//.test(trimmed)) return true;
  if (/(youtube\.com|youtu\.be)/i.test(trimmed)) return true;
  if (/^[a-z]+(?:[._:/-][a-z0-9*_-]+)+$/i.test(trimmed)) return true;
  if (/^[a-z][a-z0-9_]*$/i.test(trimmed) && !knownUiWords.has(trimmed.toLowerCase())) return true;
  if (/^[A-Z][A-Za-z0-9]+(?:Icon|Layout|Controller|Factory|Service|Badge|Card|List|Form|Button|Selector|Filter|Section)$/.test(trimmed)) return true;
  if (/^[A-Z][a-z]+(?:[A-Z][a-z]+)+$/.test(trimmed)) return true;
  if (/[A-Za-z]\d|\d\s+[A-Za-z]/.test(trimmed) && (trimmed.match(/\d/g) || []).length > 4) return true;
  if (/^[\w:.[\]/%-]+(?:\s+[\w:.[\]/%-]+)+$/.test(trimmed)) {
    const tokens = trimmed.split(/\s+/).map((token) => token.split(/[-:]/)[0].toLowerCase());
    if (tokens.every((token) => cssTokens.has(token) || /^\d+(?:\.\d+)?$/.test(token))) return true;
  }
  return false;
}

function isSuspiciousHumanText(value) {
  const normalized = value.replace(/\s+/g, ' ').trim();
  if (!/[A-Za-z]/.test(normalized)) return false;
  if (isLikelyInternal(normalized)) return false;

  const words = normalized.match(/[A-Za-z]+(?:'[A-Za-z]+)?/g) || [];
  if (words.length === 0) return false;

  if (words.length === 1) {
    const word = words[0].toLowerCase();
    return knownUiWords.has(word) && !allowedSingleWords.has(word);
  }

  const meaningful = words.filter((word) => !allowedSingleWords.has(word.toLowerCase()));
  return meaningful.length > 0;
}

function addFinding(file, line, value, source) {
  const text = value.replace(/\s+/g, ' ').trim();
  if (source === 'string literal' && /^[a-z_:-]+$/i.test(text)) return;
  if (!isSuspiciousHumanText(text)) return;
  findings.push({
    file: path.relative(root, file).replaceAll(path.sep, '/'),
    line,
    text,
    source,
  });
}

function lineNumber(source, index) {
  return source.slice(0, index).split(/\r?\n/).length;
}

function scanTemplate(file, source, template) {
  const start = source.indexOf(template);
  const attrPattern = /(?<![:@])\b(?:placeholder|title|aria-label|alt)\s*=\s*["']([^"']+)["']/g;
  for (const match of template.matchAll(attrPattern)) {
    addFinding(file, lineNumber(source, start + match.index), match[1], 'template attribute');
  }

  const textOnly = template
    .replace(/<script[\s\S]*?<\/script>/gi, '')
    .replace(/<style[\s\S]*?<\/style>/gi, '')
    .replace(/<\/?[\w:-]+(?:\s+(?:"[^"]*"|'[^']*'|[^'">])*)*\s*\/?>/g, '\n')
    .replace(/{{[\s\S]*?}}/g, '\n');

  let offset = 0;
  for (const chunk of textOnly.split(/\n+/)) {
    const index = template.indexOf(chunk, offset);
    if (index >= 0) offset = index + chunk.length;
    addFinding(file, lineNumber(source, start + Math.max(index, 0)), chunk, 'template text');
  }

}

function scanScriptStrings(file, source) {
  const cleaned = stripComments(source);
  const stringPattern = /(['"`])((?:\\.|(?!\1)[\s\S])*?)\1/g;
  for (const match of cleaned.matchAll(stringPattern)) {
    const quote = match[1];
    const value = match[2];
    const line = lineNumber(cleaned, match.index);
    const lineText = cleaned.split(/\r?\n/)[line - 1] || '';

    if (/\bimport\b|\bfrom\b|\brequire\s*\(|\broute\s*\(/.test(lineText)) continue;
    if (/\b(?:class|className|classes)\s*[:=]/.test(lineText)) continue;
    if (quote === '`' && /\$\{/.test(value)) continue;

    addFinding(file, line, value, 'string literal');
  }
}

function scanFile(file) {
  const source = fs.readFileSync(file, 'utf8');
  const cleaned = stripComments(source);
  if (path.extname(file) === '.vue') {
    const templateMatch = cleaned.match(/<template[^>]*>([\s\S]*?)<\/template>/i);
    if (templateMatch) scanTemplate(file, cleaned, templateMatch[1]);
    const scripts = [...cleaned.matchAll(/<script[^>]*>([\s\S]*?)<\/script>/gi)].map((match) => match[1]).join('\n');
    scanScriptStrings(file, scripts);
    return;
  }
  scanScriptStrings(file, cleaned);
}

if (!fs.existsSync(scanRoot)) {
  console.error('Không tìm thấy thư mục resources/js.');
  process.exit(1);
}

walk(scanRoot);

if (findings.length > 0) {
  console.error('Phát hiện chuỗi tiếng Anh có thể hiển thị cho người dùng:');
  for (const finding of findings) {
    console.error(`- ${finding.file}:${finding.line} [${finding.source}] ${finding.text}`);
  }
  process.exit(1);
}

console.log('Không phát hiện chuỗi tiếng Anh UI đáng nghi trong resources/js.');
