// The page chunks are fetched from the theme's own script folder as WordPress names it, never from wherever the
// entry happened to be served from: a plugin that moves or combines scripts would otherwise point every chunk
// at a folder that does not hold it. Imported first, before any module that loads a chunk.
const base = window.prairieAsh && window.prairieAsh.scriptBase;
if (base) __webpack_public_path__ = base;
