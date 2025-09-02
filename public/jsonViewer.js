// JSON Viewer
function renderJSONViewer(obj) {
  const rightPanel = document.getElementById("rightPanel");
  rightPanel.innerHTML = '';
  rightPanel.appendChild(renderJSON(obj,0));
}

function countChildren(value) {
  if (typeof value === 'object' && value !== null) {
    return Array.isArray(value) ? value.length : Object.keys(value).length;
  }
  return 0;
}

function renderJSON(value, indent) {
  indent = indent || 0;
  const indentStr = '  '.repeat(indent);

  if (typeof value === 'object' && value !== null) {
    const container = document.createElement('span');
    container.__value = value;
    const isArray = Array.isArray(value);

    const openSpan = document.createElement('span');
    openSpan.textContent = isArray ? '[' : '{';
    openSpan.style.cursor = 'pointer';
    container.appendChild(openSpan);

    const foldedSpan = document.createElement('span');
    foldedSpan.className = 'folded';
    container.appendChild(foldedSpan);

    const childrenSpan = document.createElement('span');
    childrenSpan.className = 'children';
    childrenSpan.appendChild(document.createTextNode('\n'));

    const keys = isArray ? value : Object.keys(value);
    keys.forEach((key, idx) => {
      const row = document.createElement('span');
      row.className = 'node';
      row.appendChild(document.createTextNode(indentStr + '  '));

      if (!isArray) {
        const keySpan = document.createElement('span');
        keySpan.textContent = key + ': ';
        keySpan.className = 'key';
        row.appendChild(keySpan);
        row.appendChild(renderJSON(value[key], indent + 1));
      } else {
        row.appendChild(renderJSON(value[key], indent + 1));
      }

      if (idx < keys.length - 1) row.appendChild(document.createTextNode(','));
      row.appendChild(document.createTextNode('\n'));
      childrenSpan.appendChild(row);
    });

    container.appendChild(childrenSpan);
    container.appendChild(document.createTextNode(indentStr + (isArray ? ']' : '}')));

    const toggleFunc = () => {
      const collapsed = childrenSpan.style.display !== 'none';
      if (collapsed) {
        childrenSpan.style.display = 'none';
        const count = countChildren(value);
        foldedSpan.textContent = (isArray ? '[…]' : '{…}') + (count>0 ? ` (${count})` : '');
      } else {
        childrenSpan.style.display = '';
        foldedSpan.textContent = '';
      }
    };

    openSpan.onclick = toggleFunc;
    foldedSpan.onclick = toggleFunc;

    return container;
  } else {
    return renderPrimitive(value);
  }
}

function renderPrimitive(value) {
  const span = document.createElement('span');
  if (typeof value === 'string') { span.textContent = `"${value}"`; span.className='string'; }
  else if (typeof value === 'number') { span.textContent = value; span.className='number'; }
  else if (typeof value === 'boolean') { span.textContent = value; span.className='boolean'; }
  else if (value === null) { span.textContent = 'null'; span.className='null'; }
  return span;
}
