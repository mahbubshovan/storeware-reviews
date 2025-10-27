(function polyfill() {
  const relList = document.createElement("link").relList;
  if (relList && relList.supports && relList.supports("modulepreload")) return;
  for (const link of document.querySelectorAll('link[rel="modulepreload"]')) processPreload(link);
  new MutationObserver((mutations) => {
    for (const mutation of mutations) {
      if (mutation.type !== "childList") continue;
      for (const node of mutation.addedNodes) if (node.tagName === "LINK" && node.rel === "modulepreload") processPreload(node);
    }
  }).observe(document, {
    childList: true,
    subtree: true
  });
  function getFetchOpts(link) {
    const fetchOpts = {};
    if (link.integrity) fetchOpts.integrity = link.integrity;
    if (link.referrerPolicy) fetchOpts.referrerPolicy = link.referrerPolicy;
    if (link.crossOrigin === "use-credentials") fetchOpts.credentials = "include";
    else if (link.crossOrigin === "anonymous") fetchOpts.credentials = "omit";
    else fetchOpts.credentials = "same-origin";
    return fetchOpts;
  }
  function processPreload(link) {
    if (link.ep) return;
    link.ep = true;
    const fetchOpts = getFetchOpts(link);
    fetch(link.href, fetchOpts);
  }
})();
(function polyfill2() {
  const relList = document.createElement("link").relList;
  if (relList && relList.supports && relList.supports("modulepreload")) return;
  for (const link of document.querySelectorAll('link[rel="modulepreload"]')) processPreload(link);
  new MutationObserver((mutations) => {
    for (const mutation of mutations) {
      if (mutation.type !== "childList") continue;
      for (const node of mutation.addedNodes) if (node.tagName === "LINK" && node.rel === "modulepreload") processPreload(node);
    }
  }).observe(document, {
    childList: true,
    subtree: true
  });
  function getFetchOpts(link) {
    const fetchOpts = {};
    if (link.integrity) fetchOpts.integrity = link.integrity;
    if (link.referrerPolicy) fetchOpts.referrerPolicy = link.referrerPolicy;
    if (link.crossOrigin === "use-credentials") fetchOpts.credentials = "include";
    else if (link.crossOrigin === "anonymous") fetchOpts.credentials = "omit";
    else fetchOpts.credentials = "same-origin";
    return fetchOpts;
  }
  function processPreload(link) {
    if (link.ep) return;
    link.ep = true;
    const fetchOpts = getFetchOpts(link);
    fetch(link.href, fetchOpts);
  }
})();
(function polyfill22() {
  const relList = document.createElement("link").relList;
  if (relList && relList.supports && relList.supports("modulepreload")) return;
  for (const link of document.querySelectorAll('link[rel="modulepreload"]')) processPreload(link);
  new MutationObserver((mutations) => {
    for (const mutation of mutations) {
      if (mutation.type !== "childList") continue;
      for (const node of mutation.addedNodes) if (node.tagName === "LINK" && node.rel === "modulepreload") processPreload(node);
    }
  }).observe(document, {
    childList: true,
    subtree: true
  });
  function getFetchOpts(link) {
    const fetchOpts = {};
    if (link.integrity) fetchOpts.integrity = link.integrity;
    if (link.referrerPolicy) fetchOpts.referrerPolicy = link.referrerPolicy;
    if (link.crossOrigin === "use-credentials") fetchOpts.credentials = "include";
    else if (link.crossOrigin === "anonymous") fetchOpts.credentials = "omit";
    else fetchOpts.credentials = "same-origin";
    return fetchOpts;
  }
  function processPreload(link) {
    if (link.ep) return;
    link.ep = true;
    const fetchOpts = getFetchOpts(link);
    fetch(link.href, fetchOpts);
  }
})();
(function polyfill222() {
  const relList = document.createElement("link").relList;
  if (relList && relList.supports && relList.supports("modulepreload")) return;
  for (const link of document.querySelectorAll('link[rel="modulepreload"]')) processPreload(link);
  new MutationObserver((mutations) => {
    for (const mutation of mutations) {
      if (mutation.type !== "childList") continue;
      for (const node of mutation.addedNodes) if (node.tagName === "LINK" && node.rel === "modulepreload") processPreload(node);
    }
  }).observe(document, {
    childList: true,
    subtree: true
  });
  function getFetchOpts(link) {
    const fetchOpts = {};
    if (link.integrity) fetchOpts.integrity = link.integrity;
    if (link.referrerPolicy) fetchOpts.referrerPolicy = link.referrerPolicy;
    if (link.crossOrigin === "use-credentials") fetchOpts.credentials = "include";
    else if (link.crossOrigin === "anonymous") fetchOpts.credentials = "omit";
    else fetchOpts.credentials = "same-origin";
    return fetchOpts;
  }
  function processPreload(link) {
    if (link.ep) return;
    link.ep = true;
    const fetchOpts = getFetchOpts(link);
    fetch(link.href, fetchOpts);
  }
})();
(function() {
  const P = document.createElement("link").relList;
  if (P && P.supports && P.supports("modulepreload")) return;
  for (const c of document.querySelectorAll('link[rel="modulepreload"]')) o(c);
  new MutationObserver((c) => {
    for (const k of c) if (k.type === "childList") for (const se of k.addedNodes) se.tagName === "LINK" && se.rel === "modulepreload" && o(se);
  }).observe(document, { childList: true, subtree: true });
  function _(c) {
    const k = {};
    return c.integrity && (k.integrity = c.integrity), c.referrerPolicy && (k.referrerPolicy = c.referrerPolicy), c.crossOrigin === "use-credentials" ? k.credentials = "include" : c.crossOrigin === "anonymous" ? k.credentials = "omit" : k.credentials = "same-origin", k;
  }
  function o(c) {
    if (c.ep) return;
    c.ep = true;
    const k = _(c);
    fetch(c.href, k);
  }
})();
(function() {
  const m = document.createElement("link").relList;
  if (m && m.supports && m.supports("modulepreload")) return;
  for (const o of document.querySelectorAll('link[rel="modulepreload"]')) _(o);
  new MutationObserver((o) => {
    for (const c of o) if (c.type === "childList") for (const k of c.addedNodes) k.tagName === "LINK" && k.rel === "modulepreload" && _(k);
  }).observe(document, { childList: true, subtree: true });
  function P(o) {
    const c = {};
    return o.integrity && (c.integrity = o.integrity), o.referrerPolicy && (c.referrerPolicy = o.referrerPolicy), o.crossOrigin === "use-credentials" ? c.credentials = "include" : o.crossOrigin === "anonymous" ? c.credentials = "omit" : c.credentials = "same-origin", c;
  }
  function _(o) {
    if (o.ep) return;
    o.ep = true;
    const c = P(o);
    fetch(o.href, c);
  }
})();
(function() {
  const m = document.createElement("link").relList;
  if (m && m.supports && m.supports("modulepreload")) return;
  for (const o of document.querySelectorAll('link[rel="modulepreload"]')) _(o);
  new MutationObserver((o) => {
    for (const c of o) if (c.type === "childList") for (const k of c.addedNodes) k.tagName === "LINK" && k.rel === "modulepreload" && _(k);
  }).observe(document, { childList: true, subtree: true });
  function P(o) {
    const c = {};
    return o.integrity && (c.integrity = o.integrity), o.referrerPolicy && (c.referrerPolicy = o.referrerPolicy), o.crossOrigin === "use-credentials" ? c.credentials = "include" : o.crossOrigin === "anonymous" ? c.credentials = "omit" : c.credentials = "same-origin", c;
  }
  function _(o) {
    if (o.ep) return;
    o.ep = true;
    const c = P(o);
    fetch(o.href, c);
  }
})();
(function() {
  const m = document.createElement("link").relList;
  if (m && m.supports && m.supports("modulepreload")) return;
  for (const o of document.querySelectorAll('link[rel="modulepreload"]')) _(o);
  new MutationObserver((o) => {
    for (const c of o) if (c.type === "childList") for (const k of c.addedNodes) k.tagName === "LINK" && k.rel === "modulepreload" && _(k);
  }).observe(document, { childList: true, subtree: true });
  function P(o) {
    const c = {};
    return o.integrity && (c.integrity = o.integrity), o.referrerPolicy && (c.referrerPolicy = o.referrerPolicy), o.crossOrigin === "use-credentials" ? c.credentials = "include" : o.crossOrigin === "anonymous" ? c.credentials = "omit" : c.credentials = "same-origin", c;
  }
  function _(o) {
    if (o.ep) return;
    o.ep = true;
    const c = P(o);
    fetch(o.href, c);
  }
})();
(function() {
  const m = document.createElement("link").relList;
  if (m && m.supports && m.supports("modulepreload")) return;
  for (const o of document.querySelectorAll('link[rel="modulepreload"]')) _(o);
  new MutationObserver((o) => {
    for (const c of o) if (c.type === "childList") for (const k of c.addedNodes) k.tagName === "LINK" && k.rel === "modulepreload" && _(k);
  }).observe(document, { childList: true, subtree: true });
  function P(o) {
    const c = {};
    return o.integrity && (c.integrity = o.integrity), o.referrerPolicy && (c.referrerPolicy = o.referrerPolicy), o.crossOrigin === "use-credentials" ? c.credentials = "include" : o.crossOrigin === "anonymous" ? c.credentials = "omit" : c.credentials = "same-origin", c;
  }
  function _(o) {
    if (o.ep) return;
    o.ep = true;
    const c = P(o);
    fetch(o.href, c);
  }
})();
(function() {
  const m = document.createElement("link").relList;
  if (m && m.supports && m.supports("modulepreload")) return;
  for (const o of document.querySelectorAll('link[rel="modulepreload"]')) _(o);
  new MutationObserver((o) => {
    for (const c of o) if (c.type === "childList") for (const k of c.addedNodes) k.tagName === "LINK" && k.rel === "modulepreload" && _(k);
  }).observe(document, { childList: true, subtree: true });
  function P(o) {
    const c = {};
    return o.integrity && (c.integrity = o.integrity), o.referrerPolicy && (c.referrerPolicy = o.referrerPolicy), o.crossOrigin === "use-credentials" ? c.credentials = "include" : o.crossOrigin === "anonymous" ? c.credentials = "omit" : c.credentials = "same-origin", c;
  }
  function _(o) {
    if (o.ep) return;
    o.ep = true;
    const c = P(o);
    fetch(o.href, c);
  }
})();
(function() {
  const m = document.createElement("link").relList;
  if (m && m.supports && m.supports("modulepreload")) return;
  for (const o of document.querySelectorAll('link[rel="modulepreload"]')) _(o);
  new MutationObserver((o) => {
    for (const c of o) if (c.type === "childList") for (const k of c.addedNodes) k.tagName === "LINK" && k.rel === "modulepreload" && _(k);
  }).observe(document, { childList: true, subtree: true });
  function P(o) {
    const c = {};
    return o.integrity && (c.integrity = o.integrity), o.referrerPolicy && (c.referrerPolicy = o.referrerPolicy), o.crossOrigin === "use-credentials" ? c.credentials = "include" : o.crossOrigin === "anonymous" ? c.credentials = "omit" : c.credentials = "same-origin", c;
  }
  function _(o) {
    if (o.ep) return;
    o.ep = true;
    const c = P(o);
    fetch(o.href, c);
  }
})();
(function() {
  const m = document.createElement("link").relList;
  if (m && m.supports && m.supports("modulepreload")) return;
  for (const o of document.querySelectorAll('link[rel="modulepreload"]')) _(o);
  new MutationObserver((o) => {
    for (const c of o) if (c.type === "childList") for (const k of c.addedNodes) k.tagName === "LINK" && k.rel === "modulepreload" && _(k);
  }).observe(document, { childList: true, subtree: true });
  function P(o) {
    const c = {};
    return o.integrity && (c.integrity = o.integrity), o.referrerPolicy && (c.referrerPolicy = o.referrerPolicy), o.crossOrigin === "use-credentials" ? c.credentials = "include" : o.crossOrigin === "anonymous" ? c.credentials = "omit" : c.credentials = "same-origin", c;
  }
  function _(o) {
    if (o.ep) return;
    o.ep = true;
    const c = P(o);
    fetch(o.href, c);
  }
})();
(function() {
  const m = document.createElement("link").relList;
  if (m && m.supports && m.supports("modulepreload")) return;
  for (const o of document.querySelectorAll('link[rel="modulepreload"]')) _(o);
  new MutationObserver((o) => {
    for (const c of o) if (c.type === "childList") for (const k of c.addedNodes) k.tagName === "LINK" && k.rel === "modulepreload" && _(k);
  }).observe(document, { childList: true, subtree: true });
  function P(o) {
    const c = {};
    return o.integrity && (c.integrity = o.integrity), o.referrerPolicy && (c.referrerPolicy = o.referrerPolicy), o.crossOrigin === "use-credentials" ? c.credentials = "include" : o.crossOrigin === "anonymous" ? c.credentials = "omit" : c.credentials = "same-origin", c;
  }
  function _(o) {
    if (o.ep) return;
    o.ep = true;
    const c = P(o);
    fetch(o.href, c);
  }
})();
(function() {
  const m = document.createElement("link").relList;
  if (m && m.supports && m.supports("modulepreload")) return;
  for (const o of document.querySelectorAll('link[rel="modulepreload"]')) _(o);
  new MutationObserver((o) => {
    for (const c of o) if (c.type === "childList") for (const k of c.addedNodes) k.tagName === "LINK" && k.rel === "modulepreload" && _(k);
  }).observe(document, { childList: true, subtree: true });
  function P(o) {
    const c = {};
    return o.integrity && (c.integrity = o.integrity), o.referrerPolicy && (c.referrerPolicy = o.referrerPolicy), o.crossOrigin === "use-credentials" ? c.credentials = "include" : o.crossOrigin === "anonymous" ? c.credentials = "omit" : c.credentials = "same-origin", c;
  }
  function _(o) {
    if (o.ep) return;
    o.ep = true;
    const c = P(o);
    fetch(o.href, c);
  }
})();
(function() {
  const m = document.createElement("link").relList;
  if (m && m.supports && m.supports("modulepreload")) return;
  for (const o of document.querySelectorAll('link[rel="modulepreload"]')) _(o);
  new MutationObserver((o) => {
    for (const c of o) if (c.type === "childList") for (const k of c.addedNodes) k.tagName === "LINK" && k.rel === "modulepreload" && _(k);
  }).observe(document, { childList: true, subtree: true });
  function P(o) {
    const c = {};
    return o.integrity && (c.integrity = o.integrity), o.referrerPolicy && (c.referrerPolicy = o.referrerPolicy), o.crossOrigin === "use-credentials" ? c.credentials = "include" : o.crossOrigin === "anonymous" ? c.credentials = "omit" : c.credentials = "same-origin", c;
  }
  function _(o) {
    if (o.ep) return;
    o.ep = true;
    const c = P(o);
    fetch(o.href, c);
  }
})();
(function() {
  const m = document.createElement("link").relList;
  if (m && m.supports && m.supports("modulepreload")) return;
  for (const o of document.querySelectorAll('link[rel="modulepreload"]')) _(o);
  new MutationObserver((o) => {
    for (const c of o) if (c.type === "childList") for (const k of c.addedNodes) k.tagName === "LINK" && k.rel === "modulepreload" && _(k);
  }).observe(document, { childList: true, subtree: true });
  function P(o) {
    const c = {};
    return o.integrity && (c.integrity = o.integrity), o.referrerPolicy && (c.referrerPolicy = o.referrerPolicy), o.crossOrigin === "use-credentials" ? c.credentials = "include" : o.crossOrigin === "anonymous" ? c.credentials = "omit" : c.credentials = "same-origin", c;
  }
  function _(o) {
    if (o.ep) return;
    o.ep = true;
    const c = P(o);
    fetch(o.href, c);
  }
})();
(function() {
  const m = document.createElement("link").relList;
  if (m && m.supports && m.supports("modulepreload")) return;
  for (const o of document.querySelectorAll('link[rel="modulepreload"]')) _(o);
  new MutationObserver((o) => {
    for (const c of o) if (c.type === "childList") for (const k of c.addedNodes) k.tagName === "LINK" && k.rel === "modulepreload" && _(k);
  }).observe(document, { childList: true, subtree: true });
  function P(o) {
    const c = {};
    return o.integrity && (c.integrity = o.integrity), o.referrerPolicy && (c.referrerPolicy = o.referrerPolicy), o.crossOrigin === "use-credentials" ? c.credentials = "include" : o.crossOrigin === "anonymous" ? c.credentials = "omit" : c.credentials = "same-origin", c;
  }
  function _(o) {
    if (o.ep) return;
    o.ep = true;
    const c = P(o);
    fetch(o.href, c);
  }
})();
(function() {
  const m = document.createElement("link").relList;
  if (m && m.supports && m.supports("modulepreload")) return;
  for (const o of document.querySelectorAll('link[rel="modulepreload"]')) _(o);
  new MutationObserver((o) => {
    for (const c of o) if (c.type === "childList") for (const k of c.addedNodes) k.tagName === "LINK" && k.rel === "modulepreload" && _(k);
  }).observe(document, { childList: true, subtree: true });
  function P(o) {
    const c = {};
    return o.integrity && (c.integrity = o.integrity), o.referrerPolicy && (c.referrerPolicy = o.referrerPolicy), o.crossOrigin === "use-credentials" ? c.credentials = "include" : o.crossOrigin === "anonymous" ? c.credentials = "omit" : c.credentials = "same-origin", c;
  }
  function _(o) {
    if (o.ep) return;
    o.ep = true;
    const c = P(o);
    fetch(o.href, c);
  }
})();
(function() {
  const m = document.createElement("link").relList;
  if (m && m.supports && m.supports("modulepreload")) return;
  for (const o of document.querySelectorAll('link[rel="modulepreload"]')) _(o);
  new MutationObserver((o) => {
    for (const c of o) if (c.type === "childList") for (const k of c.addedNodes) k.tagName === "LINK" && k.rel === "modulepreload" && _(k);
  }).observe(document, { childList: true, subtree: true });
  function P(o) {
    const c = {};
    return o.integrity && (c.integrity = o.integrity), o.referrerPolicy && (c.referrerPolicy = o.referrerPolicy), o.crossOrigin === "use-credentials" ? c.credentials = "include" : o.crossOrigin === "anonymous" ? c.credentials = "omit" : c.credentials = "same-origin", c;
  }
  function _(o) {
    if (o.ep) return;
    o.ep = true;
    const c = P(o);
    fetch(o.href, c);
  }
})();
(function() {
  const m = document.createElement("link").relList;
  if (m && m.supports && m.supports("modulepreload")) return;
  for (const o of document.querySelectorAll('link[rel="modulepreload"]')) _(o);
  new MutationObserver((o) => {
    for (const c of o) if (c.type === "childList") for (const k of c.addedNodes) k.tagName === "LINK" && k.rel === "modulepreload" && _(k);
  }).observe(document, { childList: true, subtree: true });
  function P(o) {
    const c = {};
    return o.integrity && (c.integrity = o.integrity), o.referrerPolicy && (c.referrerPolicy = o.referrerPolicy), o.crossOrigin === "use-credentials" ? c.credentials = "include" : o.crossOrigin === "anonymous" ? c.credentials = "omit" : c.credentials = "same-origin", c;
  }
  function _(o) {
    if (o.ep) return;
    o.ep = true;
    const c = P(o);
    fetch(o.href, c);
  }
})();
(function() {
  const m = document.createElement("link").relList;
  if (m && m.supports && m.supports("modulepreload")) return;
  for (const o of document.querySelectorAll('link[rel="modulepreload"]')) _(o);
  new MutationObserver((o) => {
    for (const c of o) if (c.type === "childList") for (const k of c.addedNodes) k.tagName === "LINK" && k.rel === "modulepreload" && _(k);
  }).observe(document, { childList: true, subtree: true });
  function P(o) {
    const c = {};
    return o.integrity && (c.integrity = o.integrity), o.referrerPolicy && (c.referrerPolicy = o.referrerPolicy), o.crossOrigin === "use-credentials" ? c.credentials = "include" : o.crossOrigin === "anonymous" ? c.credentials = "omit" : c.credentials = "same-origin", c;
  }
  function _(o) {
    if (o.ep) return;
    o.ep = true;
    const c = P(o);
    fetch(o.href, c);
  }
})();
(function() {
  const m = document.createElement("link").relList;
  if (m && m.supports && m.supports("modulepreload")) return;
  for (const o of document.querySelectorAll('link[rel="modulepreload"]')) _(o);
  new MutationObserver((o) => {
    for (const c of o) if (c.type === "childList") for (const k of c.addedNodes) k.tagName === "LINK" && k.rel === "modulepreload" && _(k);
  }).observe(document, { childList: true, subtree: true });
  function P(o) {
    const c = {};
    return o.integrity && (c.integrity = o.integrity), o.referrerPolicy && (c.referrerPolicy = o.referrerPolicy), o.crossOrigin === "use-credentials" ? c.credentials = "include" : o.crossOrigin === "anonymous" ? c.credentials = "omit" : c.credentials = "same-origin", c;
  }
  function _(o) {
    if (o.ep) return;
    o.ep = true;
    const c = P(o);
    fetch(o.href, c);
  }
})();
var qd = { exports: {} }, ya = {};
/**
* @license React
* react-jsx-runtime.production.js
*
* Copyright (c) Meta Platforms, Inc. and affiliates.
*
* This source code is licensed under the MIT license found in the
* LICENSE file in the root directory of this source tree.
*/
var Kd;
function zh() {
  if (Kd) return ya;
  Kd = 1;
  var m = Symbol.for("react.transitional.element"), P = Symbol.for("react.fragment");
  function _(o, c, k) {
    var se = null;
    if (k !== void 0 && (se = "" + k), c.key !== void 0 && (se = "" + c.key), "key" in c) {
      k = {};
      for (var ie in c) ie !== "key" && (k[ie] = c[ie]);
    } else k = c;
    return c = k.ref, { $$typeof: m, type: o, key: se, ref: c !== void 0 ? c : null, props: k };
  }
  return ya.Fragment = P, ya.jsx = _, ya.jsxs = _, ya;
}
var Vd;
function Ph() {
  return Vd || (Vd = 1, qd.exports = zh()), qd.exports;
}
var s = Ph(), Qd = { exports: {} }, Z = {};
/**
* @license React
* react.production.js
*
* Copyright (c) Meta Platforms, Inc. and affiliates.
*
* This source code is licensed under the MIT license found in the
* LICENSE file in the root directory of this source tree.
*/
var Yd;
function Lh() {
  if (Yd) return Z;
  Yd = 1;
  var m = Symbol.for("react.transitional.element"), P = Symbol.for("react.portal"), _ = Symbol.for("react.fragment"), o = Symbol.for("react.strict_mode"), c = Symbol.for("react.profiler"), k = Symbol.for("react.consumer"), se = Symbol.for("react.context"), ie = Symbol.for("react.forward_ref"), D = Symbol.for("react.suspense"), N = Symbol.for("react.memo"), R = Symbol.for("react.lazy"), K = Symbol.iterator;
  function O(f) {
    return f === null || typeof f != "object" ? null : (f = K && f[K] || f["@@iterator"], typeof f == "function" ? f : null);
  }
  var V = { isMounted: function() {
    return false;
  }, enqueueForceUpdate: function() {
  }, enqueueReplaceState: function() {
  }, enqueueSetState: function() {
  } }, W = Object.assign, fe = {};
  function ae(f, j, F) {
    this.props = f, this.context = j, this.refs = fe, this.updater = F || V;
  }
  ae.prototype.isReactComponent = {}, ae.prototype.setState = function(f, j) {
    if (typeof f != "object" && typeof f != "function" && f != null) throw Error("takes an object of state variables to update or a function which returns an object of state variables.");
    this.updater.enqueueSetState(this, f, j, "setState");
  }, ae.prototype.forceUpdate = function(f) {
    this.updater.enqueueForceUpdate(this, f, "forceUpdate");
  };
  function we() {
  }
  we.prototype = ae.prototype;
  function Fe(f, j, F) {
    this.props = f, this.context = j, this.refs = fe, this.updater = F || V;
  }
  var Ee = Fe.prototype = new we();
  Ee.constructor = Fe, W(Ee, ae.prototype), Ee.isPureReactComponent = true;
  var Ae = Array.isArray, X = { H: null, A: null, T: null, S: null, V: null }, Ne = Object.prototype.hasOwnProperty;
  function T(f, j, F, A, B, ue) {
    return F = ue.ref, { $$typeof: m, type: f, key: j, ref: F !== void 0 ? F : null, props: ue };
  }
  function C(f, j) {
    return T(f.type, j, void 0, void 0, void 0, f.props);
  }
  function L(f) {
    return typeof f == "object" && f !== null && f.$$typeof === m;
  }
  function J(f) {
    var j = { "=": "=0", ":": "=2" };
    return "$" + f.replace(/[=:]/g, function(F) {
      return j[F];
    });
  }
  var pe = /\/+/g;
  function ke(f, j) {
    return typeof f == "object" && f !== null && f.key != null ? J("" + f.key) : j.toString(36);
  }
  function Ct() {
  }
  function U(f) {
    switch (f.status) {
      case "fulfilled":
        return f.value;
      case "rejected":
        throw f.reason;
      default:
        switch (typeof f.status == "string" ? f.then(Ct, Ct) : (f.status = "pending", f.then(function(j) {
          f.status === "pending" && (f.status = "fulfilled", f.value = j);
        }, function(j) {
          f.status === "pending" && (f.status = "rejected", f.reason = j);
        })), f.status) {
          case "fulfilled":
            return f.value;
          case "rejected":
            throw f.reason;
        }
    }
    throw f;
  }
  function oe(f, j, F, A, B) {
    var ue = typeof f;
    (ue === "undefined" || ue === "boolean") && (f = null);
    var Y = false;
    if (f === null) Y = true;
    else switch (ue) {
      case "bigint":
      case "string":
      case "number":
        Y = true;
        break;
      case "object":
        switch (f.$$typeof) {
          case m:
          case P:
            Y = true;
            break;
          case R:
            return Y = f._init, oe(Y(f._payload), j, F, A, B);
        }
    }
    if (Y) return B = B(f), Y = A === "" ? "." + ke(f, 0) : A, Ae(B) ? (F = "", Y != null && (F = Y.replace(pe, "$&/") + "/"), oe(B, j, F, "", function(qt) {
      return qt;
    })) : B != null && (L(B) && (B = C(B, F + (B.key == null || f && f.key === B.key ? "" : ("" + B.key).replace(pe, "$&/") + "/") + Y)), j.push(B)), 1;
    Y = 0;
    var tt = A === "" ? "." : A + ":";
    if (Ae(f)) for (var _e = 0; _e < f.length; _e++) A = f[_e], ue = tt + ke(A, _e), Y += oe(A, j, F, ue, B);
    else if (_e = O(f), typeof _e == "function") for (f = _e.call(f), _e = 0; !(A = f.next()).done; ) A = A.value, ue = tt + ke(A, _e++), Y += oe(A, j, F, ue, B);
    else if (ue === "object") {
      if (typeof f.then == "function") return oe(U(f), j, F, A, B);
      throw j = String(f), Error("Objects are not valid as a React child (found: " + (j === "[object Object]" ? "object with keys {" + Object.keys(f).join(", ") + "}" : j) + "). If you meant to render a collection of children, use an array instead.");
    }
    return Y;
  }
  function x(f, j, F) {
    if (f == null) return f;
    var A = [], B = 0;
    return oe(f, A, "", "", function(ue) {
      return j.call(F, ue, B++);
    }), A;
  }
  function z(f) {
    if (f._status === -1) {
      var j = f._result;
      j = j(), j.then(function(F) {
        (f._status === 0 || f._status === -1) && (f._status = 1, f._result = F);
      }, function(F) {
        (f._status === 0 || f._status === -1) && (f._status = 2, f._result = F);
      }), f._status === -1 && (f._status = 0, f._result = j);
    }
    if (f._status === 1) return f._result.default;
    throw f._result;
  }
  var q = typeof reportError == "function" ? reportError : function(f) {
    if (typeof window == "object" && typeof window.ErrorEvent == "function") {
      var j = new window.ErrorEvent("error", { bubbles: true, cancelable: true, message: typeof f == "object" && f !== null && typeof f.message == "string" ? String(f.message) : String(f), error: f });
      if (!window.dispatchEvent(j)) return;
    } else if (typeof process == "object" && typeof process.emit == "function") {
      process.emit("uncaughtException", f);
      return;
    }
    console.error(f);
  };
  function be() {
  }
  return Z.Children = { map: x, forEach: function(f, j, F) {
    x(f, function() {
      j.apply(this, arguments);
    }, F);
  }, count: function(f) {
    var j = 0;
    return x(f, function() {
      j++;
    }), j;
  }, toArray: function(f) {
    return x(f, function(j) {
      return j;
    }) || [];
  }, only: function(f) {
    if (!L(f)) throw Error("React.Children.only expected to receive a single React element child.");
    return f;
  } }, Z.Component = ae, Z.Fragment = _, Z.Profiler = c, Z.PureComponent = Fe, Z.StrictMode = o, Z.Suspense = D, Z.__CLIENT_INTERNALS_DO_NOT_USE_OR_WARN_USERS_THEY_CANNOT_UPGRADE = X, Z.__COMPILER_RUNTIME = { __proto__: null, c: function(f) {
    return X.H.useMemoCache(f);
  } }, Z.cache = function(f) {
    return function() {
      return f.apply(null, arguments);
    };
  }, Z.cloneElement = function(f, j, F) {
    if (f == null) throw Error("The argument must be a React element, but you passed " + f + ".");
    var A = W({}, f.props), B = f.key, ue = void 0;
    if (j != null) for (Y in j.ref !== void 0 && (ue = void 0), j.key !== void 0 && (B = "" + j.key), j) !Ne.call(j, Y) || Y === "key" || Y === "__self" || Y === "__source" || Y === "ref" && j.ref === void 0 || (A[Y] = j[Y]);
    var Y = arguments.length - 2;
    if (Y === 1) A.children = F;
    else if (1 < Y) {
      for (var tt = Array(Y), _e = 0; _e < Y; _e++) tt[_e] = arguments[_e + 2];
      A.children = tt;
    }
    return T(f.type, B, void 0, void 0, ue, A);
  }, Z.createContext = function(f) {
    return f = { $$typeof: se, _currentValue: f, _currentValue2: f, _threadCount: 0, Provider: null, Consumer: null }, f.Provider = f, f.Consumer = { $$typeof: k, _context: f }, f;
  }, Z.createElement = function(f, j, F) {
    var A, B = {}, ue = null;
    if (j != null) for (A in j.key !== void 0 && (ue = "" + j.key), j) Ne.call(j, A) && A !== "key" && A !== "__self" && A !== "__source" && (B[A] = j[A]);
    var Y = arguments.length - 2;
    if (Y === 1) B.children = F;
    else if (1 < Y) {
      for (var tt = Array(Y), _e = 0; _e < Y; _e++) tt[_e] = arguments[_e + 2];
      B.children = tt;
    }
    if (f && f.defaultProps) for (A in Y = f.defaultProps, Y) B[A] === void 0 && (B[A] = Y[A]);
    return T(f, ue, void 0, void 0, null, B);
  }, Z.createRef = function() {
    return { current: null };
  }, Z.forwardRef = function(f) {
    return { $$typeof: ie, render: f };
  }, Z.isValidElement = L, Z.lazy = function(f) {
    return { $$typeof: R, _payload: { _status: -1, _result: f }, _init: z };
  }, Z.memo = function(f, j) {
    return { $$typeof: N, type: f, compare: j === void 0 ? null : j };
  }, Z.startTransition = function(f) {
    var j = X.T, F = {};
    X.T = F;
    try {
      var A = f(), B = X.S;
      B !== null && B(F, A), typeof A == "object" && A !== null && typeof A.then == "function" && A.then(be, q);
    } catch (ue) {
      q(ue);
    } finally {
      X.T = j;
    }
  }, Z.unstable_useCacheRefresh = function() {
    return X.H.useCacheRefresh();
  }, Z.use = function(f) {
    return X.H.use(f);
  }, Z.useActionState = function(f, j, F) {
    return X.H.useActionState(f, j, F);
  }, Z.useCallback = function(f, j) {
    return X.H.useCallback(f, j);
  }, Z.useContext = function(f) {
    return X.H.useContext(f);
  }, Z.useDebugValue = function() {
  }, Z.useDeferredValue = function(f, j) {
    return X.H.useDeferredValue(f, j);
  }, Z.useEffect = function(f, j, F) {
    var A = X.H;
    if (typeof F == "function") throw Error("useEffect CRUD overload is not enabled in this build of React.");
    return A.useEffect(f, j);
  }, Z.useId = function() {
    return X.H.useId();
  }, Z.useImperativeHandle = function(f, j, F) {
    return X.H.useImperativeHandle(f, j, F);
  }, Z.useInsertionEffect = function(f, j) {
    return X.H.useInsertionEffect(f, j);
  }, Z.useLayoutEffect = function(f, j) {
    return X.H.useLayoutEffect(f, j);
  }, Z.useMemo = function(f, j) {
    return X.H.useMemo(f, j);
  }, Z.useOptimistic = function(f, j) {
    return X.H.useOptimistic(f, j);
  }, Z.useReducer = function(f, j, F) {
    return X.H.useReducer(f, j, F);
  }, Z.useRef = function(f) {
    return X.H.useRef(f);
  }, Z.useState = function(f) {
    return X.H.useState(f);
  }, Z.useSyncExternalStore = function(f, j, F) {
    return X.H.useSyncExternalStore(f, j, F);
  }, Z.useTransition = function() {
    return X.H.useTransition();
  }, Z.version = "19.1.1", Z;
}
var Gd;
function Zo() {
  return Gd || (Gd = 1, Qd.exports = Lh()), Qd.exports;
}
var G = Zo(), Go = { exports: {} }, va = {}, Xd = { exports: {} }, Zd = {};
/**
* @license React
* scheduler.production.js
*
* Copyright (c) Meta Platforms, Inc. and affiliates.
*
* This source code is licensed under the MIT license found in the
* LICENSE file in the root directory of this source tree.
*/
var Jd;
function Th() {
  return Jd || (Jd = 1, (function(m) {
    function P(x, z) {
      var q = x.length;
      x.push(z);
      e: for (; 0 < q; ) {
        var be = q - 1 >>> 1, f = x[be];
        if (0 < c(f, z)) x[be] = z, x[q] = f, q = be;
        else break e;
      }
    }
    function _(x) {
      return x.length === 0 ? null : x[0];
    }
    function o(x) {
      if (x.length === 0) return null;
      var z = x[0], q = x.pop();
      if (q !== z) {
        x[0] = q;
        e: for (var be = 0, f = x.length, j = f >>> 1; be < j; ) {
          var F = 2 * (be + 1) - 1, A = x[F], B = F + 1, ue = x[B];
          if (0 > c(A, q)) B < f && 0 > c(ue, A) ? (x[be] = ue, x[B] = q, be = B) : (x[be] = A, x[F] = q, be = F);
          else if (B < f && 0 > c(ue, q)) x[be] = ue, x[B] = q, be = B;
          else break e;
        }
      }
      return z;
    }
    function c(x, z) {
      var q = x.sortIndex - z.sortIndex;
      return q !== 0 ? q : x.id - z.id;
    }
    if (m.unstable_now = void 0, typeof performance == "object" && typeof performance.now == "function") {
      var k = performance;
      m.unstable_now = function() {
        return k.now();
      };
    } else {
      var se = Date, ie = se.now();
      m.unstable_now = function() {
        return se.now() - ie;
      };
    }
    var D = [], N = [], R = 1, K = null, O = 3, V = false, W = false, fe = false, ae = false, we = typeof setTimeout == "function" ? setTimeout : null, Fe = typeof clearTimeout == "function" ? clearTimeout : null, Ee = typeof setImmediate < "u" ? setImmediate : null;
    function Ae(x) {
      for (var z = _(N); z !== null; ) {
        if (z.callback === null) o(N);
        else if (z.startTime <= x) o(N), z.sortIndex = z.expirationTime, P(D, z);
        else break;
        z = _(N);
      }
    }
    function X(x) {
      if (fe = false, Ae(x), !W) if (_(D) !== null) W = true, Ne || (Ne = true, ke());
      else {
        var z = _(N);
        z !== null && oe(X, z.startTime - x);
      }
    }
    var Ne = false, T = -1, C = 5, L = -1;
    function J() {
      return ae ? true : !(m.unstable_now() - L < C);
    }
    function pe() {
      if (ae = false, Ne) {
        var x = m.unstable_now();
        L = x;
        var z = true;
        try {
          e: {
            W = false, fe && (fe = false, Fe(T), T = -1), V = true;
            var q = O;
            try {
              t: {
                for (Ae(x), K = _(D); K !== null && !(K.expirationTime > x && J()); ) {
                  var be = K.callback;
                  if (typeof be == "function") {
                    K.callback = null, O = K.priorityLevel;
                    var f = be(K.expirationTime <= x);
                    if (x = m.unstable_now(), typeof f == "function") {
                      K.callback = f, Ae(x), z = true;
                      break t;
                    }
                    K === _(D) && o(D), Ae(x);
                  } else o(D);
                  K = _(D);
                }
                if (K !== null) z = true;
                else {
                  var j = _(N);
                  j !== null && oe(X, j.startTime - x), z = false;
                }
              }
              break e;
            } finally {
              K = null, O = q, V = false;
            }
            z = void 0;
          }
        } finally {
          z ? ke() : Ne = false;
        }
      }
    }
    var ke;
    if (typeof Ee == "function") ke = function() {
      Ee(pe);
    };
    else if (typeof MessageChannel < "u") {
      var Ct = new MessageChannel(), U = Ct.port2;
      Ct.port1.onmessage = pe, ke = function() {
        U.postMessage(null);
      };
    } else ke = function() {
      we(pe, 0);
    };
    function oe(x, z) {
      T = we(function() {
        x(m.unstable_now());
      }, z);
    }
    m.unstable_IdlePriority = 5, m.unstable_ImmediatePriority = 1, m.unstable_LowPriority = 4, m.unstable_NormalPriority = 3, m.unstable_Profiling = null, m.unstable_UserBlockingPriority = 2, m.unstable_cancelCallback = function(x) {
      x.callback = null;
    }, m.unstable_forceFrameRate = function(x) {
      0 > x || 125 < x ? console.error("forceFrameRate takes a positive int between 0 and 125, forcing frame rates higher than 125 fps is not supported") : C = 0 < x ? Math.floor(1e3 / x) : 5;
    }, m.unstable_getCurrentPriorityLevel = function() {
      return O;
    }, m.unstable_next = function(x) {
      switch (O) {
        case 1:
        case 2:
        case 3:
          var z = 3;
          break;
        default:
          z = O;
      }
      var q = O;
      O = z;
      try {
        return x();
      } finally {
        O = q;
      }
    }, m.unstable_requestPaint = function() {
      ae = true;
    }, m.unstable_runWithPriority = function(x, z) {
      switch (x) {
        case 1:
        case 2:
        case 3:
        case 4:
        case 5:
          break;
        default:
          x = 3;
      }
      var q = O;
      O = x;
      try {
        return z();
      } finally {
        O = q;
      }
    }, m.unstable_scheduleCallback = function(x, z, q) {
      var be = m.unstable_now();
      switch (typeof q == "object" && q !== null ? (q = q.delay, q = typeof q == "number" && 0 < q ? be + q : be) : q = be, x) {
        case 1:
          var f = -1;
          break;
        case 2:
          f = 250;
          break;
        case 5:
          f = 1073741823;
          break;
        case 4:
          f = 1e4;
          break;
        default:
          f = 5e3;
      }
      return f = q + f, x = { id: R++, callback: z, priorityLevel: x, startTime: q, expirationTime: f, sortIndex: -1 }, q > be ? (x.sortIndex = q, P(N, x), _(D) === null && x === _(N) && (fe ? (Fe(T), T = -1) : fe = true, oe(X, q - be))) : (x.sortIndex = f, P(D, x), W || V || (W = true, Ne || (Ne = true, ke()))), x;
    }, m.unstable_shouldYield = J, m.unstable_wrapCallback = function(x) {
      var z = O;
      return function() {
        var q = O;
        O = z;
        try {
          return x.apply(this, arguments);
        } finally {
          O = q;
        }
      };
    };
  })(Zd)), Zd;
}
var ef;
function Ah() {
  return ef || (ef = 1, Xd.exports = Th()), Xd.exports;
}
var Xo = { exports: {} }, Qe = {};
/**
* @license React
* react-dom.production.js
*
* Copyright (c) Meta Platforms, Inc. and affiliates.
*
* This source code is licensed under the MIT license found in the
* LICENSE file in the root directory of this source tree.
*/
var tf;
function Rh() {
  if (tf) return Qe;
  tf = 1;
  var m = Zo();
  function P(D) {
    var N = "https://react.dev/errors/" + D;
    if (1 < arguments.length) {
      N += "?args[]=" + encodeURIComponent(arguments[1]);
      for (var R = 2; R < arguments.length; R++) N += "&args[]=" + encodeURIComponent(arguments[R]);
    }
    return "Minified React error #" + D + "; visit " + N + " for the full message or use the non-minified dev environment for full errors and additional helpful warnings.";
  }
  function _() {
  }
  var o = { d: { f: _, r: function() {
    throw Error(P(522));
  }, D: _, C: _, L: _, m: _, X: _, S: _, M: _ }, p: 0, findDOMNode: null }, c = Symbol.for("react.portal");
  function k(D, N, R) {
    var K = 3 < arguments.length && arguments[3] !== void 0 ? arguments[3] : null;
    return { $$typeof: c, key: K == null ? null : "" + K, children: D, containerInfo: N, implementation: R };
  }
  var se = m.__CLIENT_INTERNALS_DO_NOT_USE_OR_WARN_USERS_THEY_CANNOT_UPGRADE;
  function ie(D, N) {
    if (D === "font") return "";
    if (typeof N == "string") return N === "use-credentials" ? N : "";
  }
  return Qe.__DOM_INTERNALS_DO_NOT_USE_OR_WARN_USERS_THEY_CANNOT_UPGRADE = o, Qe.createPortal = function(D, N) {
    var R = 2 < arguments.length && arguments[2] !== void 0 ? arguments[2] : null;
    if (!N || N.nodeType !== 1 && N.nodeType !== 9 && N.nodeType !== 11) throw Error(P(299));
    return k(D, N, null, R);
  }, Qe.flushSync = function(D) {
    var N = se.T, R = o.p;
    try {
      if (se.T = null, o.p = 2, D) return D();
    } finally {
      se.T = N, o.p = R, o.d.f();
    }
  }, Qe.preconnect = function(D, N) {
    typeof D == "string" && (N ? (N = N.crossOrigin, N = typeof N == "string" ? N === "use-credentials" ? N : "" : void 0) : N = null, o.d.C(D, N));
  }, Qe.prefetchDNS = function(D) {
    typeof D == "string" && o.d.D(D);
  }, Qe.preinit = function(D, N) {
    if (typeof D == "string" && N && typeof N.as == "string") {
      var R = N.as, K = ie(R, N.crossOrigin), O = typeof N.integrity == "string" ? N.integrity : void 0, V = typeof N.fetchPriority == "string" ? N.fetchPriority : void 0;
      R === "style" ? o.d.S(D, typeof N.precedence == "string" ? N.precedence : void 0, { crossOrigin: K, integrity: O, fetchPriority: V }) : R === "script" && o.d.X(D, { crossOrigin: K, integrity: O, fetchPriority: V, nonce: typeof N.nonce == "string" ? N.nonce : void 0 });
    }
  }, Qe.preinitModule = function(D, N) {
    if (typeof D == "string") if (typeof N == "object" && N !== null) {
      if (N.as == null || N.as === "script") {
        var R = ie(N.as, N.crossOrigin);
        o.d.M(D, { crossOrigin: R, integrity: typeof N.integrity == "string" ? N.integrity : void 0, nonce: typeof N.nonce == "string" ? N.nonce : void 0 });
      }
    } else N == null && o.d.M(D);
  }, Qe.preload = function(D, N) {
    if (typeof D == "string" && typeof N == "object" && N !== null && typeof N.as == "string") {
      var R = N.as, K = ie(R, N.crossOrigin);
      o.d.L(D, R, { crossOrigin: K, integrity: typeof N.integrity == "string" ? N.integrity : void 0, nonce: typeof N.nonce == "string" ? N.nonce : void 0, type: typeof N.type == "string" ? N.type : void 0, fetchPriority: typeof N.fetchPriority == "string" ? N.fetchPriority : void 0, referrerPolicy: typeof N.referrerPolicy == "string" ? N.referrerPolicy : void 0, imageSrcSet: typeof N.imageSrcSet == "string" ? N.imageSrcSet : void 0, imageSizes: typeof N.imageSizes == "string" ? N.imageSizes : void 0, media: typeof N.media == "string" ? N.media : void 0 });
    }
  }, Qe.preloadModule = function(D, N) {
    if (typeof D == "string") if (N) {
      var R = ie(N.as, N.crossOrigin);
      o.d.m(D, { as: typeof N.as == "string" && N.as !== "script" ? N.as : void 0, crossOrigin: R, integrity: typeof N.integrity == "string" ? N.integrity : void 0 });
    } else o.d.m(D);
  }, Qe.requestFormReset = function(D) {
    o.d.r(D);
  }, Qe.unstable_batchedUpdates = function(D, N) {
    return D(N);
  }, Qe.useFormState = function(D, N, R) {
    return se.H.useFormState(D, N, R);
  }, Qe.useFormStatus = function() {
    return se.H.useHostTransitionStatus();
  }, Qe.version = "19.1.1", Qe;
}
var nf;
function Oh() {
  if (nf) return Xo.exports;
  nf = 1;
  function m() {
    if (!(typeof __REACT_DEVTOOLS_GLOBAL_HOOK__ > "u" || typeof __REACT_DEVTOOLS_GLOBAL_HOOK__.checkDCE != "function")) try {
      __REACT_DEVTOOLS_GLOBAL_HOOK__.checkDCE(m);
    } catch (P) {
      console.error(P);
    }
  }
  return m(), Xo.exports = Rh(), Xo.exports;
}
/**
* @license React
* react-dom-client.production.js
*
* Copyright (c) Meta Platforms, Inc. and affiliates.
*
* This source code is licensed under the MIT license found in the
* LICENSE file in the root directory of this source tree.
*/
var rf;
function Fh() {
  if (rf) return va;
  rf = 1;
  var m = Ah(), P = Zo(), _ = Oh();
  function o(e) {
    var t = "https://react.dev/errors/" + e;
    if (1 < arguments.length) {
      t += "?args[]=" + encodeURIComponent(arguments[1]);
      for (var n = 2; n < arguments.length; n++) t += "&args[]=" + encodeURIComponent(arguments[n]);
    }
    return "Minified React error #" + e + "; visit " + t + " for the full message or use the non-minified dev environment for full errors and additional helpful warnings.";
  }
  function c(e) {
    return !(!e || e.nodeType !== 1 && e.nodeType !== 9 && e.nodeType !== 11);
  }
  function k(e) {
    var t = e, n = e;
    if (e.alternate) for (; t.return; ) t = t.return;
    else {
      e = t;
      do
        t = e, (t.flags & 4098) !== 0 && (n = t.return), e = t.return;
      while (e);
    }
    return t.tag === 3 ? n : null;
  }
  function se(e) {
    if (e.tag === 13) {
      var t = e.memoizedState;
      if (t === null && (e = e.alternate, e !== null && (t = e.memoizedState)), t !== null) return t.dehydrated;
    }
    return null;
  }
  function ie(e) {
    if (k(e) !== e) throw Error(o(188));
  }
  function D(e) {
    var t = e.alternate;
    if (!t) {
      if (t = k(e), t === null) throw Error(o(188));
      return t !== e ? null : e;
    }
    for (var n = e, r = t; ; ) {
      var a = n.return;
      if (a === null) break;
      var l = a.alternate;
      if (l === null) {
        if (r = a.return, r !== null) {
          n = r;
          continue;
        }
        break;
      }
      if (a.child === l.child) {
        for (l = a.child; l; ) {
          if (l === n) return ie(a), e;
          if (l === r) return ie(a), t;
          l = l.sibling;
        }
        throw Error(o(188));
      }
      if (n.return !== r.return) n = a, r = l;
      else {
        for (var i = false, u = a.child; u; ) {
          if (u === n) {
            i = true, n = a, r = l;
            break;
          }
          if (u === r) {
            i = true, r = a, n = l;
            break;
          }
          u = u.sibling;
        }
        if (!i) {
          for (u = l.child; u; ) {
            if (u === n) {
              i = true, n = l, r = a;
              break;
            }
            if (u === r) {
              i = true, r = l, n = a;
              break;
            }
            u = u.sibling;
          }
          if (!i) throw Error(o(189));
        }
      }
      if (n.alternate !== r) throw Error(o(190));
    }
    if (n.tag !== 3) throw Error(o(188));
    return n.stateNode.current === n ? e : t;
  }
  function N(e) {
    var t = e.tag;
    if (t === 5 || t === 26 || t === 27 || t === 6) return e;
    for (e = e.child; e !== null; ) {
      if (t = N(e), t !== null) return t;
      e = e.sibling;
    }
    return null;
  }
  var R = Object.assign, K = Symbol.for("react.element"), O = Symbol.for("react.transitional.element"), V = Symbol.for("react.portal"), W = Symbol.for("react.fragment"), fe = Symbol.for("react.strict_mode"), ae = Symbol.for("react.profiler"), we = Symbol.for("react.provider"), Fe = Symbol.for("react.consumer"), Ee = Symbol.for("react.context"), Ae = Symbol.for("react.forward_ref"), X = Symbol.for("react.suspense"), Ne = Symbol.for("react.suspense_list"), T = Symbol.for("react.memo"), C = Symbol.for("react.lazy"), L = Symbol.for("react.activity"), J = Symbol.for("react.memo_cache_sentinel"), pe = Symbol.iterator;
  function ke(e) {
    return e === null || typeof e != "object" ? null : (e = pe && e[pe] || e["@@iterator"], typeof e == "function" ? e : null);
  }
  var Ct = Symbol.for("react.client.reference");
  function U(e) {
    if (e == null) return null;
    if (typeof e == "function") return e.$$typeof === Ct ? null : e.displayName || e.name || null;
    if (typeof e == "string") return e;
    switch (e) {
      case W:
        return "Fragment";
      case ae:
        return "Profiler";
      case fe:
        return "StrictMode";
      case X:
        return "Suspense";
      case Ne:
        return "SuspenseList";
      case L:
        return "Activity";
    }
    if (typeof e == "object") switch (e.$$typeof) {
      case V:
        return "Portal";
      case Ee:
        return (e.displayName || "Context") + ".Provider";
      case Fe:
        return (e._context.displayName || "Context") + ".Consumer";
      case Ae:
        var t = e.render;
        return e = e.displayName, e || (e = t.displayName || t.name || "", e = e !== "" ? "ForwardRef(" + e + ")" : "ForwardRef"), e;
      case T:
        return t = e.displayName || null, t !== null ? t : U(e.type) || "Memo";
      case C:
        t = e._payload, e = e._init;
        try {
          return U(e(t));
        } catch {
        }
    }
    return null;
  }
  var oe = Array.isArray, x = P.__CLIENT_INTERNALS_DO_NOT_USE_OR_WARN_USERS_THEY_CANNOT_UPGRADE, z = _.__DOM_INTERNALS_DO_NOT_USE_OR_WARN_USERS_THEY_CANNOT_UPGRADE, q = { pending: false, data: null, method: null, action: null }, be = [], f = -1;
  function j(e) {
    return { current: e };
  }
  function F(e) {
    0 > f || (e.current = be[f], be[f] = null, f--);
  }
  function A(e, t) {
    f++, be[f] = e.current, e.current = t;
  }
  var B = j(null), ue = j(null), Y = j(null), tt = j(null);
  function _e(e, t) {
    switch (A(Y, t), A(ue, e), A(B, null), t.nodeType) {
      case 9:
      case 11:
        e = (e = t.documentElement) && (e = e.namespaceURI) ? bd(e) : 0;
        break;
      default:
        if (e = t.tagName, t = t.namespaceURI) t = bd(t), e = xd(t, e);
        else switch (e) {
          case "svg":
            e = 1;
            break;
          case "math":
            e = 2;
            break;
          default:
            e = 0;
        }
    }
    F(B), A(B, e);
  }
  function qt() {
    F(B), F(ue), F(Y);
  }
  function Rl(e) {
    e.memoizedState !== null && A(tt, e);
    var t = B.current, n = xd(t, e.type);
    t !== n && (A(ue, e), A(B, n));
  }
  function ba(e) {
    ue.current === e && (F(B), F(ue)), tt.current === e && (F(tt), fa._currentValue = q);
  }
  var Ol = Object.prototype.hasOwnProperty, Fl = m.unstable_scheduleCallback, Dl = m.unstable_cancelCallback, of = m.unstable_shouldYield, sf = m.unstable_requestPaint, St = m.unstable_now, uf = m.unstable_getCurrentPriorityLevel, Jo = m.unstable_ImmediatePriority, es = m.unstable_UserBlockingPriority, xa = m.unstable_NormalPriority, cf = m.unstable_LowPriority, ts = m.unstable_IdlePriority, df = m.log, ff = m.unstable_setDisableYieldValue, xr = null, nt = null;
  function Kt(e) {
    if (typeof df == "function" && ff(e), nt && typeof nt.setStrictMode == "function") try {
      nt.setStrictMode(xr, e);
    } catch {
    }
  }
  var rt = Math.clz32 ? Math.clz32 : mf, pf = Math.log, hf = Math.LN2;
  function mf(e) {
    return e >>>= 0, e === 0 ? 32 : 31 - (pf(e) / hf | 0) | 0;
  }
  var wa = 256, ka = 4194304;
  function yn(e) {
    var t = e & 42;
    if (t !== 0) return t;
    switch (e & -e) {
      case 1:
        return 1;
      case 2:
        return 2;
      case 4:
        return 4;
      case 8:
        return 8;
      case 16:
        return 16;
      case 32:
        return 32;
      case 64:
        return 64;
      case 128:
        return 128;
      case 256:
      case 512:
      case 1024:
      case 2048:
      case 4096:
      case 8192:
      case 16384:
      case 32768:
      case 65536:
      case 131072:
      case 262144:
      case 524288:
      case 1048576:
      case 2097152:
        return e & 4194048;
      case 4194304:
      case 8388608:
      case 16777216:
      case 33554432:
        return e & 62914560;
      case 67108864:
        return 67108864;
      case 134217728:
        return 134217728;
      case 268435456:
        return 268435456;
      case 536870912:
        return 536870912;
      case 1073741824:
        return 0;
      default:
        return e;
    }
  }
  function Sa(e, t, n) {
    var r = e.pendingLanes;
    if (r === 0) return 0;
    var a = 0, l = e.suspendedLanes, i = e.pingedLanes;
    e = e.warmLanes;
    var u = r & 134217727;
    return u !== 0 ? (r = u & ~l, r !== 0 ? a = yn(r) : (i &= u, i !== 0 ? a = yn(i) : n || (n = u & ~e, n !== 0 && (a = yn(n))))) : (u = r & ~l, u !== 0 ? a = yn(u) : i !== 0 ? a = yn(i) : n || (n = r & ~e, n !== 0 && (a = yn(n)))), a === 0 ? 0 : t !== 0 && t !== a && (t & l) === 0 && (l = a & -a, n = t & -t, l >= n || l === 32 && (n & 4194048) !== 0) ? t : a;
  }
  function wr(e, t) {
    return (e.pendingLanes & ~(e.suspendedLanes & ~e.pingedLanes) & t) === 0;
  }
  function gf(e, t) {
    switch (e) {
      case 1:
      case 2:
      case 4:
      case 8:
      case 64:
        return t + 250;
      case 16:
      case 32:
      case 128:
      case 256:
      case 512:
      case 1024:
      case 2048:
      case 4096:
      case 8192:
      case 16384:
      case 32768:
      case 65536:
      case 131072:
      case 262144:
      case 524288:
      case 1048576:
      case 2097152:
        return t + 5e3;
      case 4194304:
      case 8388608:
      case 16777216:
      case 33554432:
        return -1;
      case 67108864:
      case 134217728:
      case 268435456:
      case 536870912:
      case 1073741824:
        return -1;
      default:
        return -1;
    }
  }
  function ns() {
    var e = wa;
    return wa <<= 1, (wa & 4194048) === 0 && (wa = 256), e;
  }
  function rs() {
    var e = ka;
    return ka <<= 1, (ka & 62914560) === 0 && (ka = 4194304), e;
  }
  function Ml(e) {
    for (var t = [], n = 0; 31 > n; n++) t.push(e);
    return t;
  }
  function kr(e, t) {
    e.pendingLanes |= t, t !== 268435456 && (e.suspendedLanes = 0, e.pingedLanes = 0, e.warmLanes = 0);
  }
  function yf(e, t, n, r, a, l) {
    var i = e.pendingLanes;
    e.pendingLanes = n, e.suspendedLanes = 0, e.pingedLanes = 0, e.warmLanes = 0, e.expiredLanes &= n, e.entangledLanes &= n, e.errorRecoveryDisabledLanes &= n, e.shellSuspendCounter = 0;
    var u = e.entanglements, d = e.expirationTimes, y = e.hiddenUpdates;
    for (n = i & ~n; 0 < n; ) {
      var w = 31 - rt(n), E = 1 << w;
      u[w] = 0, d[w] = -1;
      var v = y[w];
      if (v !== null) for (y[w] = null, w = 0; w < v.length; w++) {
        var b = v[w];
        b !== null && (b.lane &= -536870913);
      }
      n &= ~E;
    }
    r !== 0 && as(e, r, 0), l !== 0 && a === 0 && e.tag !== 0 && (e.suspendedLanes |= l & ~(i & ~t));
  }
  function as(e, t, n) {
    e.pendingLanes |= t, e.suspendedLanes &= ~t;
    var r = 31 - rt(t);
    e.entangledLanes |= t, e.entanglements[r] = e.entanglements[r] | 1073741824 | n & 4194090;
  }
  function ls(e, t) {
    var n = e.entangledLanes |= t;
    for (e = e.entanglements; n; ) {
      var r = 31 - rt(n), a = 1 << r;
      a & t | e[r] & t && (e[r] |= t), n &= ~a;
    }
  }
  function Il(e) {
    switch (e) {
      case 2:
        e = 1;
        break;
      case 8:
        e = 4;
        break;
      case 32:
        e = 16;
        break;
      case 256:
      case 512:
      case 1024:
      case 2048:
      case 4096:
      case 8192:
      case 16384:
      case 32768:
      case 65536:
      case 131072:
      case 262144:
      case 524288:
      case 1048576:
      case 2097152:
      case 4194304:
      case 8388608:
      case 16777216:
      case 33554432:
        e = 128;
        break;
      case 268435456:
        e = 134217728;
        break;
      default:
        e = 0;
    }
    return e;
  }
  function Ul(e) {
    return e &= -e, 2 < e ? 8 < e ? (e & 134217727) !== 0 ? 32 : 268435456 : 8 : 2;
  }
  function is() {
    var e = z.p;
    return e !== 0 ? e : (e = window.event, e === void 0 ? 32 : Id(e.type));
  }
  function vf(e, t) {
    var n = z.p;
    try {
      return z.p = e, t();
    } finally {
      z.p = n;
    }
  }
  var Vt = Math.random().toString(36).slice(2), Ke = "__reactFiber$" + Vt, Ge = "__reactProps$" + Vt, On = "__reactContainer$" + Vt, Bl = "__reactEvents$" + Vt, bf = "__reactListeners$" + Vt, xf = "__reactHandles$" + Vt, os = "__reactResources$" + Vt, Sr = "__reactMarker$" + Vt;
  function Wl(e) {
    delete e[Ke], delete e[Ge], delete e[Bl], delete e[bf], delete e[xf];
  }
  function Fn(e) {
    var t = e[Ke];
    if (t) return t;
    for (var n = e.parentNode; n; ) {
      if (t = n[On] || n[Ke]) {
        if (n = t.alternate, t.child !== null || n !== null && n.child !== null) for (e = Nd(e); e !== null; ) {
          if (n = e[Ke]) return n;
          e = Nd(e);
        }
        return t;
      }
      e = n, n = e.parentNode;
    }
    return null;
  }
  function Dn(e) {
    if (e = e[Ke] || e[On]) {
      var t = e.tag;
      if (t === 5 || t === 6 || t === 13 || t === 26 || t === 27 || t === 3) return e;
    }
    return null;
  }
  function Nr(e) {
    var t = e.tag;
    if (t === 5 || t === 26 || t === 27 || t === 6) return e.stateNode;
    throw Error(o(33));
  }
  function Mn(e) {
    var t = e[os];
    return t || (t = e[os] = { hoistableStyles: /* @__PURE__ */ new Map(), hoistableScripts: /* @__PURE__ */ new Map() }), t;
  }
  function Ie(e) {
    e[Sr] = true;
  }
  var ss = /* @__PURE__ */ new Set(), us = {};
  function vn(e, t) {
    In(e, t), In(e + "Capture", t);
  }
  function In(e, t) {
    for (us[e] = t, e = 0; e < t.length; e++) ss.add(t[e]);
  }
  var wf = RegExp("^[:A-Z_a-z\\u00C0-\\u00D6\\u00D8-\\u00F6\\u00F8-\\u02FF\\u0370-\\u037D\\u037F-\\u1FFF\\u200C-\\u200D\\u2070-\\u218F\\u2C00-\\u2FEF\\u3001-\\uD7FF\\uF900-\\uFDCF\\uFDF0-\\uFFFD][:A-Z_a-z\\u00C0-\\u00D6\\u00D8-\\u00F6\\u00F8-\\u02FF\\u0370-\\u037D\\u037F-\\u1FFF\\u200C-\\u200D\\u2070-\\u218F\\u2C00-\\u2FEF\\u3001-\\uD7FF\\uF900-\\uFDCF\\uFDF0-\\uFFFD\\-.0-9\\u00B7\\u0300-\\u036F\\u203F-\\u2040]*$"), cs = {}, ds = {};
  function kf(e) {
    return Ol.call(ds, e) ? true : Ol.call(cs, e) ? false : wf.test(e) ? ds[e] = true : (cs[e] = true, false);
  }
  function Na(e, t, n) {
    if (kf(t)) if (n === null) e.removeAttribute(t);
    else {
      switch (typeof n) {
        case "undefined":
        case "function":
        case "symbol":
          e.removeAttribute(t);
          return;
        case "boolean":
          var r = t.toLowerCase().slice(0, 5);
          if (r !== "data-" && r !== "aria-") {
            e.removeAttribute(t);
            return;
          }
      }
      e.setAttribute(t, "" + n);
    }
  }
  function Ea(e, t, n) {
    if (n === null) e.removeAttribute(t);
    else {
      switch (typeof n) {
        case "undefined":
        case "function":
        case "symbol":
        case "boolean":
          e.removeAttribute(t);
          return;
      }
      e.setAttribute(t, "" + n);
    }
  }
  function zt(e, t, n, r) {
    if (r === null) e.removeAttribute(n);
    else {
      switch (typeof r) {
        case "undefined":
        case "function":
        case "symbol":
        case "boolean":
          e.removeAttribute(n);
          return;
      }
      e.setAttributeNS(t, n, "" + r);
    }
  }
  var Hl, fs;
  function Un(e) {
    if (Hl === void 0) try {
      throw Error();
    } catch (n) {
      var t = n.stack.trim().match(/\n( *(at )?)/);
      Hl = t && t[1] || "", fs = -1 < n.stack.indexOf(`
    at`) ? " (<anonymous>)" : -1 < n.stack.indexOf("@") ? "@unknown:0:0" : "";
    }
    return `
` + Hl + e + fs;
  }
  var $l = false;
  function ql(e, t) {
    if (!e || $l) return "";
    $l = true;
    var n = Error.prepareStackTrace;
    Error.prepareStackTrace = void 0;
    try {
      var r = { DetermineComponentFrameRoot: function() {
        try {
          if (t) {
            var E = function() {
              throw Error();
            };
            if (Object.defineProperty(E.prototype, "props", { set: function() {
              throw Error();
            } }), typeof Reflect == "object" && Reflect.construct) {
              try {
                Reflect.construct(E, []);
              } catch (b) {
                var v = b;
              }
              Reflect.construct(e, [], E);
            } else {
              try {
                E.call();
              } catch (b) {
                v = b;
              }
              e.call(E.prototype);
            }
          } else {
            try {
              throw Error();
            } catch (b) {
              v = b;
            }
            (E = e()) && typeof E.catch == "function" && E.catch(function() {
            });
          }
        } catch (b) {
          if (b && v && typeof b.stack == "string") return [b.stack, v.stack];
        }
        return [null, null];
      } };
      r.DetermineComponentFrameRoot.displayName = "DetermineComponentFrameRoot";
      var a = Object.getOwnPropertyDescriptor(r.DetermineComponentFrameRoot, "name");
      a && a.configurable && Object.defineProperty(r.DetermineComponentFrameRoot, "name", { value: "DetermineComponentFrameRoot" });
      var l = r.DetermineComponentFrameRoot(), i = l[0], u = l[1];
      if (i && u) {
        var d = i.split(`
`), y = u.split(`
`);
        for (a = r = 0; r < d.length && !d[r].includes("DetermineComponentFrameRoot"); ) r++;
        for (; a < y.length && !y[a].includes("DetermineComponentFrameRoot"); ) a++;
        if (r === d.length || a === y.length) for (r = d.length - 1, a = y.length - 1; 1 <= r && 0 <= a && d[r] !== y[a]; ) a--;
        for (; 1 <= r && 0 <= a; r--, a--) if (d[r] !== y[a]) {
          if (r !== 1 || a !== 1) do
            if (r--, a--, 0 > a || d[r] !== y[a]) {
              var w = `
` + d[r].replace(" at new ", " at ");
              return e.displayName && w.includes("<anonymous>") && (w = w.replace("<anonymous>", e.displayName)), w;
            }
          while (1 <= r && 0 <= a);
          break;
        }
      }
    } finally {
      $l = false, Error.prepareStackTrace = n;
    }
    return (n = e ? e.displayName || e.name : "") ? Un(n) : "";
  }
  function Sf(e) {
    switch (e.tag) {
      case 26:
      case 27:
      case 5:
        return Un(e.type);
      case 16:
        return Un("Lazy");
      case 13:
        return Un("Suspense");
      case 19:
        return Un("SuspenseList");
      case 0:
      case 15:
        return ql(e.type, false);
      case 11:
        return ql(e.type.render, false);
      case 1:
        return ql(e.type, true);
      case 31:
        return Un("Activity");
      default:
        return "";
    }
  }
  function ps(e) {
    try {
      var t = "";
      do
        t += Sf(e), e = e.return;
      while (e);
      return t;
    } catch (n) {
      return `
Error generating stack: ` + n.message + `
` + n.stack;
    }
  }
  function dt(e) {
    switch (typeof e) {
      case "bigint":
      case "boolean":
      case "number":
      case "string":
      case "undefined":
        return e;
      case "object":
        return e;
      default:
        return "";
    }
  }
  function hs(e) {
    var t = e.type;
    return (e = e.nodeName) && e.toLowerCase() === "input" && (t === "checkbox" || t === "radio");
  }
  function Nf(e) {
    var t = hs(e) ? "checked" : "value", n = Object.getOwnPropertyDescriptor(e.constructor.prototype, t), r = "" + e[t];
    if (!e.hasOwnProperty(t) && typeof n < "u" && typeof n.get == "function" && typeof n.set == "function") {
      var a = n.get, l = n.set;
      return Object.defineProperty(e, t, { configurable: true, get: function() {
        return a.call(this);
      }, set: function(i) {
        r = "" + i, l.call(this, i);
      } }), Object.defineProperty(e, t, { enumerable: n.enumerable }), { getValue: function() {
        return r;
      }, setValue: function(i) {
        r = "" + i;
      }, stopTracking: function() {
        e._valueTracker = null, delete e[t];
      } };
    }
  }
  function _a(e) {
    e._valueTracker || (e._valueTracker = Nf(e));
  }
  function ms(e) {
    if (!e) return false;
    var t = e._valueTracker;
    if (!t) return true;
    var n = t.getValue(), r = "";
    return e && (r = hs(e) ? e.checked ? "true" : "false" : e.value), e = r, e !== n ? (t.setValue(e), true) : false;
  }
  function ja(e) {
    if (e = e || (typeof document < "u" ? document : void 0), typeof e > "u") return null;
    try {
      return e.activeElement || e.body;
    } catch {
      return e.body;
    }
  }
  var Ef = /[\n"\\]/g;
  function bt(e) {
    return e.replace(Ef, function(t) {
      return "\\" + t.charCodeAt(0).toString(16) + " ";
    });
  }
  function Kl(e, t, n, r, a, l, i, u) {
    e.name = "", i != null && typeof i != "function" && typeof i != "symbol" && typeof i != "boolean" ? e.type = i : e.removeAttribute("type"), t != null ? i === "number" ? (t === 0 && e.value === "" || e.value != t) && (e.value = "" + dt(t)) : e.value !== "" + dt(t) && (e.value = "" + dt(t)) : i !== "submit" && i !== "reset" || e.removeAttribute("value"), t != null ? Vl(e, i, dt(t)) : n != null ? Vl(e, i, dt(n)) : r != null && e.removeAttribute("value"), a == null && l != null && (e.defaultChecked = !!l), a != null && (e.checked = a && typeof a != "function" && typeof a != "symbol"), u != null && typeof u != "function" && typeof u != "symbol" && typeof u != "boolean" ? e.name = "" + dt(u) : e.removeAttribute("name");
  }
  function gs(e, t, n, r, a, l, i, u) {
    if (l != null && typeof l != "function" && typeof l != "symbol" && typeof l != "boolean" && (e.type = l), t != null || n != null) {
      if (!(l !== "submit" && l !== "reset" || t != null)) return;
      n = n != null ? "" + dt(n) : "", t = t != null ? "" + dt(t) : n, u || t === e.value || (e.value = t), e.defaultValue = t;
    }
    r = r ?? a, r = typeof r != "function" && typeof r != "symbol" && !!r, e.checked = u ? e.checked : !!r, e.defaultChecked = !!r, i != null && typeof i != "function" && typeof i != "symbol" && typeof i != "boolean" && (e.name = i);
  }
  function Vl(e, t, n) {
    t === "number" && ja(e.ownerDocument) === e || e.defaultValue === "" + n || (e.defaultValue = "" + n);
  }
  function Bn(e, t, n, r) {
    if (e = e.options, t) {
      t = {};
      for (var a = 0; a < n.length; a++) t["$" + n[a]] = true;
      for (n = 0; n < e.length; n++) a = t.hasOwnProperty("$" + e[n].value), e[n].selected !== a && (e[n].selected = a), a && r && (e[n].defaultSelected = true);
    } else {
      for (n = "" + dt(n), t = null, a = 0; a < e.length; a++) {
        if (e[a].value === n) {
          e[a].selected = true, r && (e[a].defaultSelected = true);
          return;
        }
        t !== null || e[a].disabled || (t = e[a]);
      }
      t !== null && (t.selected = true);
    }
  }
  function ys(e, t, n) {
    if (t != null && (t = "" + dt(t), t !== e.value && (e.value = t), n == null)) {
      e.defaultValue !== t && (e.defaultValue = t);
      return;
    }
    e.defaultValue = n != null ? "" + dt(n) : "";
  }
  function vs(e, t, n, r) {
    if (t == null) {
      if (r != null) {
        if (n != null) throw Error(o(92));
        if (oe(r)) {
          if (1 < r.length) throw Error(o(93));
          r = r[0];
        }
        n = r;
      }
      n == null && (n = ""), t = n;
    }
    n = dt(t), e.defaultValue = n, r = e.textContent, r === n && r !== "" && r !== null && (e.value = r);
  }
  function Wn(e, t) {
    if (t) {
      var n = e.firstChild;
      if (n && n === e.lastChild && n.nodeType === 3) {
        n.nodeValue = t;
        return;
      }
    }
    e.textContent = t;
  }
  var _f = new Set("animationIterationCount aspectRatio borderImageOutset borderImageSlice borderImageWidth boxFlex boxFlexGroup boxOrdinalGroup columnCount columns flex flexGrow flexPositive flexShrink flexNegative flexOrder gridArea gridRow gridRowEnd gridRowSpan gridRowStart gridColumn gridColumnEnd gridColumnSpan gridColumnStart fontWeight lineClamp lineHeight opacity order orphans scale tabSize widows zIndex zoom fillOpacity floodOpacity stopOpacity strokeDasharray strokeDashoffset strokeMiterlimit strokeOpacity strokeWidth MozAnimationIterationCount MozBoxFlex MozBoxFlexGroup MozLineClamp msAnimationIterationCount msFlex msZoom msFlexGrow msFlexNegative msFlexOrder msFlexPositive msFlexShrink msGridColumn msGridColumnSpan msGridRow msGridRowSpan WebkitAnimationIterationCount WebkitBoxFlex WebKitBoxFlexGroup WebkitBoxOrdinalGroup WebkitColumnCount WebkitColumns WebkitFlex WebkitFlexGrow WebkitFlexPositive WebkitFlexShrink WebkitLineClamp".split(" "));
  function bs(e, t, n) {
    var r = t.indexOf("--") === 0;
    n == null || typeof n == "boolean" || n === "" ? r ? e.setProperty(t, "") : t === "float" ? e.cssFloat = "" : e[t] = "" : r ? e.setProperty(t, n) : typeof n != "number" || n === 0 || _f.has(t) ? t === "float" ? e.cssFloat = n : e[t] = ("" + n).trim() : e[t] = n + "px";
  }
  function xs(e, t, n) {
    if (t != null && typeof t != "object") throw Error(o(62));
    if (e = e.style, n != null) {
      for (var r in n) !n.hasOwnProperty(r) || t != null && t.hasOwnProperty(r) || (r.indexOf("--") === 0 ? e.setProperty(r, "") : r === "float" ? e.cssFloat = "" : e[r] = "");
      for (var a in t) r = t[a], t.hasOwnProperty(a) && n[a] !== r && bs(e, a, r);
    } else for (var l in t) t.hasOwnProperty(l) && bs(e, l, t[l]);
  }
  function Ql(e) {
    if (e.indexOf("-") === -1) return false;
    switch (e) {
      case "annotation-xml":
      case "color-profile":
      case "font-face":
      case "font-face-src":
      case "font-face-uri":
      case "font-face-format":
      case "font-face-name":
      case "missing-glyph":
        return false;
      default:
        return true;
    }
  }
  var jf = /* @__PURE__ */ new Map([["acceptCharset", "accept-charset"], ["htmlFor", "for"], ["httpEquiv", "http-equiv"], ["crossOrigin", "crossorigin"], ["accentHeight", "accent-height"], ["alignmentBaseline", "alignment-baseline"], ["arabicForm", "arabic-form"], ["baselineShift", "baseline-shift"], ["capHeight", "cap-height"], ["clipPath", "clip-path"], ["clipRule", "clip-rule"], ["colorInterpolation", "color-interpolation"], ["colorInterpolationFilters", "color-interpolation-filters"], ["colorProfile", "color-profile"], ["colorRendering", "color-rendering"], ["dominantBaseline", "dominant-baseline"], ["enableBackground", "enable-background"], ["fillOpacity", "fill-opacity"], ["fillRule", "fill-rule"], ["floodColor", "flood-color"], ["floodOpacity", "flood-opacity"], ["fontFamily", "font-family"], ["fontSize", "font-size"], ["fontSizeAdjust", "font-size-adjust"], ["fontStretch", "font-stretch"], ["fontStyle", "font-style"], ["fontVariant", "font-variant"], ["fontWeight", "font-weight"], ["glyphName", "glyph-name"], ["glyphOrientationHorizontal", "glyph-orientation-horizontal"], ["glyphOrientationVertical", "glyph-orientation-vertical"], ["horizAdvX", "horiz-adv-x"], ["horizOriginX", "horiz-origin-x"], ["imageRendering", "image-rendering"], ["letterSpacing", "letter-spacing"], ["lightingColor", "lighting-color"], ["markerEnd", "marker-end"], ["markerMid", "marker-mid"], ["markerStart", "marker-start"], ["overlinePosition", "overline-position"], ["overlineThickness", "overline-thickness"], ["paintOrder", "paint-order"], ["panose-1", "panose-1"], ["pointerEvents", "pointer-events"], ["renderingIntent", "rendering-intent"], ["shapeRendering", "shape-rendering"], ["stopColor", "stop-color"], ["stopOpacity", "stop-opacity"], ["strikethroughPosition", "strikethrough-position"], ["strikethroughThickness", "strikethrough-thickness"], ["strokeDasharray", "stroke-dasharray"], ["strokeDashoffset", "stroke-dashoffset"], ["strokeLinecap", "stroke-linecap"], ["strokeLinejoin", "stroke-linejoin"], ["strokeMiterlimit", "stroke-miterlimit"], ["strokeOpacity", "stroke-opacity"], ["strokeWidth", "stroke-width"], ["textAnchor", "text-anchor"], ["textDecoration", "text-decoration"], ["textRendering", "text-rendering"], ["transformOrigin", "transform-origin"], ["underlinePosition", "underline-position"], ["underlineThickness", "underline-thickness"], ["unicodeBidi", "unicode-bidi"], ["unicodeRange", "unicode-range"], ["unitsPerEm", "units-per-em"], ["vAlphabetic", "v-alphabetic"], ["vHanging", "v-hanging"], ["vIdeographic", "v-ideographic"], ["vMathematical", "v-mathematical"], ["vectorEffect", "vector-effect"], ["vertAdvY", "vert-adv-y"], ["vertOriginX", "vert-origin-x"], ["vertOriginY", "vert-origin-y"], ["wordSpacing", "word-spacing"], ["writingMode", "writing-mode"], ["xmlnsXlink", "xmlns:xlink"], ["xHeight", "x-height"]]), Cf = /^[\u0000-\u001F ]*j[\r\n\t]*a[\r\n\t]*v[\r\n\t]*a[\r\n\t]*s[\r\n\t]*c[\r\n\t]*r[\r\n\t]*i[\r\n\t]*p[\r\n\t]*t[\r\n\t]*:/i;
  function Ca(e) {
    return Cf.test("" + e) ? "javascript:throw new Error('React has blocked a javascript: URL as a security precaution.')" : e;
  }
  var Yl = null;
  function Gl(e) {
    return e = e.target || e.srcElement || window, e.correspondingUseElement && (e = e.correspondingUseElement), e.nodeType === 3 ? e.parentNode : e;
  }
  var Hn = null, $n = null;
  function ws(e) {
    var t = Dn(e);
    if (t && (e = t.stateNode)) {
      var n = e[Ge] || null;
      e: switch (e = t.stateNode, t.type) {
        case "input":
          if (Kl(e, n.value, n.defaultValue, n.defaultValue, n.checked, n.defaultChecked, n.type, n.name), t = n.name, n.type === "radio" && t != null) {
            for (n = e; n.parentNode; ) n = n.parentNode;
            for (n = n.querySelectorAll('input[name="' + bt("" + t) + '"][type="radio"]'), t = 0; t < n.length; t++) {
              var r = n[t];
              if (r !== e && r.form === e.form) {
                var a = r[Ge] || null;
                if (!a) throw Error(o(90));
                Kl(r, a.value, a.defaultValue, a.defaultValue, a.checked, a.defaultChecked, a.type, a.name);
              }
            }
            for (t = 0; t < n.length; t++) r = n[t], r.form === e.form && ms(r);
          }
          break e;
        case "textarea":
          ys(e, n.value, n.defaultValue);
          break e;
        case "select":
          t = n.value, t != null && Bn(e, !!n.multiple, t, false);
      }
    }
  }
  var Xl = false;
  function ks(e, t, n) {
    if (Xl) return e(t, n);
    Xl = true;
    try {
      var r = e(t);
      return r;
    } finally {
      if (Xl = false, (Hn !== null || $n !== null) && (pl(), Hn && (t = Hn, e = $n, $n = Hn = null, ws(t), e))) for (t = 0; t < e.length; t++) ws(e[t]);
    }
  }
  function Er(e, t) {
    var n = e.stateNode;
    if (n === null) return null;
    var r = n[Ge] || null;
    if (r === null) return null;
    n = r[t];
    e: switch (t) {
      case "onClick":
      case "onClickCapture":
      case "onDoubleClick":
      case "onDoubleClickCapture":
      case "onMouseDown":
      case "onMouseDownCapture":
      case "onMouseMove":
      case "onMouseMoveCapture":
      case "onMouseUp":
      case "onMouseUpCapture":
      case "onMouseEnter":
        (r = !r.disabled) || (e = e.type, r = !(e === "button" || e === "input" || e === "select" || e === "textarea")), e = !r;
        break e;
      default:
        e = false;
    }
    if (e) return null;
    if (n && typeof n != "function") throw Error(o(231, t, typeof n));
    return n;
  }
  var Pt = !(typeof window > "u" || typeof window.document > "u" || typeof window.document.createElement > "u"), Zl = false;
  if (Pt) try {
    var _r = {};
    Object.defineProperty(_r, "passive", { get: function() {
      Zl = true;
    } }), window.addEventListener("test", _r, _r), window.removeEventListener("test", _r, _r);
  } catch {
    Zl = false;
  }
  var Qt = null, Jl = null, za = null;
  function Ss() {
    if (za) return za;
    var e, t = Jl, n = t.length, r, a = "value" in Qt ? Qt.value : Qt.textContent, l = a.length;
    for (e = 0; e < n && t[e] === a[e]; e++) ;
    var i = n - e;
    for (r = 1; r <= i && t[n - r] === a[l - r]; r++) ;
    return za = a.slice(e, 1 < r ? 1 - r : void 0);
  }
  function Pa(e) {
    var t = e.keyCode;
    return "charCode" in e ? (e = e.charCode, e === 0 && t === 13 && (e = 13)) : e = t, e === 10 && (e = 13), 32 <= e || e === 13 ? e : 0;
  }
  function La() {
    return true;
  }
  function Ns() {
    return false;
  }
  function Xe(e) {
    function t(n, r, a, l, i) {
      this._reactName = n, this._targetInst = a, this.type = r, this.nativeEvent = l, this.target = i, this.currentTarget = null;
      for (var u in e) e.hasOwnProperty(u) && (n = e[u], this[u] = n ? n(l) : l[u]);
      return this.isDefaultPrevented = (l.defaultPrevented != null ? l.defaultPrevented : l.returnValue === false) ? La : Ns, this.isPropagationStopped = Ns, this;
    }
    return R(t.prototype, { preventDefault: function() {
      this.defaultPrevented = true;
      var n = this.nativeEvent;
      n && (n.preventDefault ? n.preventDefault() : typeof n.returnValue != "unknown" && (n.returnValue = false), this.isDefaultPrevented = La);
    }, stopPropagation: function() {
      var n = this.nativeEvent;
      n && (n.stopPropagation ? n.stopPropagation() : typeof n.cancelBubble != "unknown" && (n.cancelBubble = true), this.isPropagationStopped = La);
    }, persist: function() {
    }, isPersistent: La }), t;
  }
  var bn = { eventPhase: 0, bubbles: 0, cancelable: 0, timeStamp: function(e) {
    return e.timeStamp || Date.now();
  }, defaultPrevented: 0, isTrusted: 0 }, Ta = Xe(bn), jr = R({}, bn, { view: 0, detail: 0 }), zf = Xe(jr), ei, ti, Cr, Aa = R({}, jr, { screenX: 0, screenY: 0, clientX: 0, clientY: 0, pageX: 0, pageY: 0, ctrlKey: 0, shiftKey: 0, altKey: 0, metaKey: 0, getModifierState: ri, button: 0, buttons: 0, relatedTarget: function(e) {
    return e.relatedTarget === void 0 ? e.fromElement === e.srcElement ? e.toElement : e.fromElement : e.relatedTarget;
  }, movementX: function(e) {
    return "movementX" in e ? e.movementX : (e !== Cr && (Cr && e.type === "mousemove" ? (ei = e.screenX - Cr.screenX, ti = e.screenY - Cr.screenY) : ti = ei = 0, Cr = e), ei);
  }, movementY: function(e) {
    return "movementY" in e ? e.movementY : ti;
  } }), Es = Xe(Aa), Pf = R({}, Aa, { dataTransfer: 0 }), Lf = Xe(Pf), Tf = R({}, jr, { relatedTarget: 0 }), ni = Xe(Tf), Af = R({}, bn, { animationName: 0, elapsedTime: 0, pseudoElement: 0 }), Rf = Xe(Af), Of = R({}, bn, { clipboardData: function(e) {
    return "clipboardData" in e ? e.clipboardData : window.clipboardData;
  } }), Ff = Xe(Of), Df = R({}, bn, { data: 0 }), _s = Xe(Df), Mf = { Esc: "Escape", Spacebar: " ", Left: "ArrowLeft", Up: "ArrowUp", Right: "ArrowRight", Down: "ArrowDown", Del: "Delete", Win: "OS", Menu: "ContextMenu", Apps: "ContextMenu", Scroll: "ScrollLock", MozPrintableKey: "Unidentified" }, If = { 8: "Backspace", 9: "Tab", 12: "Clear", 13: "Enter", 16: "Shift", 17: "Control", 18: "Alt", 19: "Pause", 20: "CapsLock", 27: "Escape", 32: " ", 33: "PageUp", 34: "PageDown", 35: "End", 36: "Home", 37: "ArrowLeft", 38: "ArrowUp", 39: "ArrowRight", 40: "ArrowDown", 45: "Insert", 46: "Delete", 112: "F1", 113: "F2", 114: "F3", 115: "F4", 116: "F5", 117: "F6", 118: "F7", 119: "F8", 120: "F9", 121: "F10", 122: "F11", 123: "F12", 144: "NumLock", 145: "ScrollLock", 224: "Meta" }, Uf = { Alt: "altKey", Control: "ctrlKey", Meta: "metaKey", Shift: "shiftKey" };
  function Bf(e) {
    var t = this.nativeEvent;
    return t.getModifierState ? t.getModifierState(e) : (e = Uf[e]) ? !!t[e] : false;
  }
  function ri() {
    return Bf;
  }
  var Wf = R({}, jr, { key: function(e) {
    if (e.key) {
      var t = Mf[e.key] || e.key;
      if (t !== "Unidentified") return t;
    }
    return e.type === "keypress" ? (e = Pa(e), e === 13 ? "Enter" : String.fromCharCode(e)) : e.type === "keydown" || e.type === "keyup" ? If[e.keyCode] || "Unidentified" : "";
  }, code: 0, location: 0, ctrlKey: 0, shiftKey: 0, altKey: 0, metaKey: 0, repeat: 0, locale: 0, getModifierState: ri, charCode: function(e) {
    return e.type === "keypress" ? Pa(e) : 0;
  }, keyCode: function(e) {
    return e.type === "keydown" || e.type === "keyup" ? e.keyCode : 0;
  }, which: function(e) {
    return e.type === "keypress" ? Pa(e) : e.type === "keydown" || e.type === "keyup" ? e.keyCode : 0;
  } }), Hf = Xe(Wf), $f = R({}, Aa, { pointerId: 0, width: 0, height: 0, pressure: 0, tangentialPressure: 0, tiltX: 0, tiltY: 0, twist: 0, pointerType: 0, isPrimary: 0 }), js = Xe($f), qf = R({}, jr, { touches: 0, targetTouches: 0, changedTouches: 0, altKey: 0, metaKey: 0, ctrlKey: 0, shiftKey: 0, getModifierState: ri }), Kf = Xe(qf), Vf = R({}, bn, { propertyName: 0, elapsedTime: 0, pseudoElement: 0 }), Qf = Xe(Vf), Yf = R({}, Aa, { deltaX: function(e) {
    return "deltaX" in e ? e.deltaX : "wheelDeltaX" in e ? -e.wheelDeltaX : 0;
  }, deltaY: function(e) {
    return "deltaY" in e ? e.deltaY : "wheelDeltaY" in e ? -e.wheelDeltaY : "wheelDelta" in e ? -e.wheelDelta : 0;
  }, deltaZ: 0, deltaMode: 0 }), Gf = Xe(Yf), Xf = R({}, bn, { newState: 0, oldState: 0 }), Zf = Xe(Xf), Jf = [9, 13, 27, 32], ai = Pt && "CompositionEvent" in window, zr = null;
  Pt && "documentMode" in document && (zr = document.documentMode);
  var ep = Pt && "TextEvent" in window && !zr, Cs = Pt && (!ai || zr && 8 < zr && 11 >= zr), zs = " ", Ps = false;
  function Ls(e, t) {
    switch (e) {
      case "keyup":
        return Jf.indexOf(t.keyCode) !== -1;
      case "keydown":
        return t.keyCode !== 229;
      case "keypress":
      case "mousedown":
      case "focusout":
        return true;
      default:
        return false;
    }
  }
  function Ts(e) {
    return e = e.detail, typeof e == "object" && "data" in e ? e.data : null;
  }
  var qn = false;
  function tp(e, t) {
    switch (e) {
      case "compositionend":
        return Ts(t);
      case "keypress":
        return t.which !== 32 ? null : (Ps = true, zs);
      case "textInput":
        return e = t.data, e === zs && Ps ? null : e;
      default:
        return null;
    }
  }
  function np(e, t) {
    if (qn) return e === "compositionend" || !ai && Ls(e, t) ? (e = Ss(), za = Jl = Qt = null, qn = false, e) : null;
    switch (e) {
      case "paste":
        return null;
      case "keypress":
        if (!(t.ctrlKey || t.altKey || t.metaKey) || t.ctrlKey && t.altKey) {
          if (t.char && 1 < t.char.length) return t.char;
          if (t.which) return String.fromCharCode(t.which);
        }
        return null;
      case "compositionend":
        return Cs && t.locale !== "ko" ? null : t.data;
      default:
        return null;
    }
  }
  var rp = { color: true, date: true, datetime: true, "datetime-local": true, email: true, month: true, number: true, password: true, range: true, search: true, tel: true, text: true, time: true, url: true, week: true };
  function As(e) {
    var t = e && e.nodeName && e.nodeName.toLowerCase();
    return t === "input" ? !!rp[e.type] : t === "textarea";
  }
  function Rs(e, t, n, r) {
    Hn ? $n ? $n.push(r) : $n = [r] : Hn = r, t = bl(t, "onChange"), 0 < t.length && (n = new Ta("onChange", "change", null, n, r), e.push({ event: n, listeners: t }));
  }
  var Pr = null, Lr = null;
  function ap(e) {
    hd(e, 0);
  }
  function Ra(e) {
    var t = Nr(e);
    if (ms(t)) return e;
  }
  function Os(e, t) {
    if (e === "change") return t;
  }
  var Fs = false;
  if (Pt) {
    var li;
    if (Pt) {
      var ii = "oninput" in document;
      if (!ii) {
        var Ds = document.createElement("div");
        Ds.setAttribute("oninput", "return;"), ii = typeof Ds.oninput == "function";
      }
      li = ii;
    } else li = false;
    Fs = li && (!document.documentMode || 9 < document.documentMode);
  }
  function Ms() {
    Pr && (Pr.detachEvent("onpropertychange", Is), Lr = Pr = null);
  }
  function Is(e) {
    if (e.propertyName === "value" && Ra(Lr)) {
      var t = [];
      Rs(t, Lr, e, Gl(e)), ks(ap, t);
    }
  }
  function lp(e, t, n) {
    e === "focusin" ? (Ms(), Pr = t, Lr = n, Pr.attachEvent("onpropertychange", Is)) : e === "focusout" && Ms();
  }
  function ip(e) {
    if (e === "selectionchange" || e === "keyup" || e === "keydown") return Ra(Lr);
  }
  function op(e, t) {
    if (e === "click") return Ra(t);
  }
  function sp(e, t) {
    if (e === "input" || e === "change") return Ra(t);
  }
  function up(e, t) {
    return e === t && (e !== 0 || 1 / e === 1 / t) || e !== e && t !== t;
  }
  var at = typeof Object.is == "function" ? Object.is : up;
  function Tr(e, t) {
    if (at(e, t)) return true;
    if (typeof e != "object" || e === null || typeof t != "object" || t === null) return false;
    var n = Object.keys(e), r = Object.keys(t);
    if (n.length !== r.length) return false;
    for (r = 0; r < n.length; r++) {
      var a = n[r];
      if (!Ol.call(t, a) || !at(e[a], t[a])) return false;
    }
    return true;
  }
  function Us(e) {
    for (; e && e.firstChild; ) e = e.firstChild;
    return e;
  }
  function Bs(e, t) {
    var n = Us(e);
    e = 0;
    for (var r; n; ) {
      if (n.nodeType === 3) {
        if (r = e + n.textContent.length, e <= t && r >= t) return { node: n, offset: t - e };
        e = r;
      }
      e: {
        for (; n; ) {
          if (n.nextSibling) {
            n = n.nextSibling;
            break e;
          }
          n = n.parentNode;
        }
        n = void 0;
      }
      n = Us(n);
    }
  }
  function Ws(e, t) {
    return e && t ? e === t ? true : e && e.nodeType === 3 ? false : t && t.nodeType === 3 ? Ws(e, t.parentNode) : "contains" in e ? e.contains(t) : e.compareDocumentPosition ? !!(e.compareDocumentPosition(t) & 16) : false : false;
  }
  function Hs(e) {
    e = e != null && e.ownerDocument != null && e.ownerDocument.defaultView != null ? e.ownerDocument.defaultView : window;
    for (var t = ja(e.document); t instanceof e.HTMLIFrameElement; ) {
      try {
        var n = typeof t.contentWindow.location.href == "string";
      } catch {
        n = false;
      }
      if (n) e = t.contentWindow;
      else break;
      t = ja(e.document);
    }
    return t;
  }
  function oi(e) {
    var t = e && e.nodeName && e.nodeName.toLowerCase();
    return t && (t === "input" && (e.type === "text" || e.type === "search" || e.type === "tel" || e.type === "url" || e.type === "password") || t === "textarea" || e.contentEditable === "true");
  }
  var cp = Pt && "documentMode" in document && 11 >= document.documentMode, Kn = null, si = null, Ar = null, ui = false;
  function $s(e, t, n) {
    var r = n.window === n ? n.document : n.nodeType === 9 ? n : n.ownerDocument;
    ui || Kn == null || Kn !== ja(r) || (r = Kn, "selectionStart" in r && oi(r) ? r = { start: r.selectionStart, end: r.selectionEnd } : (r = (r.ownerDocument && r.ownerDocument.defaultView || window).getSelection(), r = { anchorNode: r.anchorNode, anchorOffset: r.anchorOffset, focusNode: r.focusNode, focusOffset: r.focusOffset }), Ar && Tr(Ar, r) || (Ar = r, r = bl(si, "onSelect"), 0 < r.length && (t = new Ta("onSelect", "select", null, t, n), e.push({ event: t, listeners: r }), t.target = Kn)));
  }
  function xn(e, t) {
    var n = {};
    return n[e.toLowerCase()] = t.toLowerCase(), n["Webkit" + e] = "webkit" + t, n["Moz" + e] = "moz" + t, n;
  }
  var Vn = { animationend: xn("Animation", "AnimationEnd"), animationiteration: xn("Animation", "AnimationIteration"), animationstart: xn("Animation", "AnimationStart"), transitionrun: xn("Transition", "TransitionRun"), transitionstart: xn("Transition", "TransitionStart"), transitioncancel: xn("Transition", "TransitionCancel"), transitionend: xn("Transition", "TransitionEnd") }, ci = {}, qs = {};
  Pt && (qs = document.createElement("div").style, "AnimationEvent" in window || (delete Vn.animationend.animation, delete Vn.animationiteration.animation, delete Vn.animationstart.animation), "TransitionEvent" in window || delete Vn.transitionend.transition);
  function wn(e) {
    if (ci[e]) return ci[e];
    if (!Vn[e]) return e;
    var t = Vn[e], n;
    for (n in t) if (t.hasOwnProperty(n) && n in qs) return ci[e] = t[n];
    return e;
  }
  var Ks = wn("animationend"), Vs = wn("animationiteration"), Qs = wn("animationstart"), dp = wn("transitionrun"), fp = wn("transitionstart"), pp = wn("transitioncancel"), Ys = wn("transitionend"), Gs = /* @__PURE__ */ new Map(), di = "abort auxClick beforeToggle cancel canPlay canPlayThrough click close contextMenu copy cut drag dragEnd dragEnter dragExit dragLeave dragOver dragStart drop durationChange emptied encrypted ended error gotPointerCapture input invalid keyDown keyPress keyUp load loadedData loadedMetadata loadStart lostPointerCapture mouseDown mouseMove mouseOut mouseOver mouseUp paste pause play playing pointerCancel pointerDown pointerMove pointerOut pointerOver pointerUp progress rateChange reset resize seeked seeking stalled submit suspend timeUpdate touchCancel touchEnd touchStart volumeChange scroll toggle touchMove waiting wheel".split(" ");
  di.push("scrollEnd");
  function xt(e, t) {
    Gs.set(e, t), vn(t, [e]);
  }
  var Xs = /* @__PURE__ */ new WeakMap();
  function ft(e, t) {
    if (typeof e == "object" && e !== null) {
      var n = Xs.get(e);
      return n !== void 0 ? n : (t = { value: e, source: t, stack: ps(t) }, Xs.set(e, t), t);
    }
    return { value: e, source: t, stack: ps(t) };
  }
  var pt = [], Qn = 0, fi = 0;
  function Oa() {
    for (var e = Qn, t = fi = Qn = 0; t < e; ) {
      var n = pt[t];
      pt[t++] = null;
      var r = pt[t];
      pt[t++] = null;
      var a = pt[t];
      pt[t++] = null;
      var l = pt[t];
      if (pt[t++] = null, r !== null && a !== null) {
        var i = r.pending;
        i === null ? a.next = a : (a.next = i.next, i.next = a), r.pending = a;
      }
      l !== 0 && Zs(n, a, l);
    }
  }
  function Fa(e, t, n, r) {
    pt[Qn++] = e, pt[Qn++] = t, pt[Qn++] = n, pt[Qn++] = r, fi |= r, e.lanes |= r, e = e.alternate, e !== null && (e.lanes |= r);
  }
  function pi(e, t, n, r) {
    return Fa(e, t, n, r), Da(e);
  }
  function Yn(e, t) {
    return Fa(e, null, null, t), Da(e);
  }
  function Zs(e, t, n) {
    e.lanes |= n;
    var r = e.alternate;
    r !== null && (r.lanes |= n);
    for (var a = false, l = e.return; l !== null; ) l.childLanes |= n, r = l.alternate, r !== null && (r.childLanes |= n), l.tag === 22 && (e = l.stateNode, e === null || e._visibility & 1 || (a = true)), e = l, l = l.return;
    return e.tag === 3 ? (l = e.stateNode, a && t !== null && (a = 31 - rt(n), e = l.hiddenUpdates, r = e[a], r === null ? e[a] = [t] : r.push(t), t.lane = n | 536870912), l) : null;
  }
  function Da(e) {
    if (50 < aa) throw aa = 0, xo = null, Error(o(185));
    for (var t = e.return; t !== null; ) e = t, t = e.return;
    return e.tag === 3 ? e.stateNode : null;
  }
  var Gn = {};
  function hp(e, t, n, r) {
    this.tag = e, this.key = n, this.sibling = this.child = this.return = this.stateNode = this.type = this.elementType = null, this.index = 0, this.refCleanup = this.ref = null, this.pendingProps = t, this.dependencies = this.memoizedState = this.updateQueue = this.memoizedProps = null, this.mode = r, this.subtreeFlags = this.flags = 0, this.deletions = null, this.childLanes = this.lanes = 0, this.alternate = null;
  }
  function lt(e, t, n, r) {
    return new hp(e, t, n, r);
  }
  function hi(e) {
    return e = e.prototype, !(!e || !e.isReactComponent);
  }
  function Lt(e, t) {
    var n = e.alternate;
    return n === null ? (n = lt(e.tag, t, e.key, e.mode), n.elementType = e.elementType, n.type = e.type, n.stateNode = e.stateNode, n.alternate = e, e.alternate = n) : (n.pendingProps = t, n.type = e.type, n.flags = 0, n.subtreeFlags = 0, n.deletions = null), n.flags = e.flags & 65011712, n.childLanes = e.childLanes, n.lanes = e.lanes, n.child = e.child, n.memoizedProps = e.memoizedProps, n.memoizedState = e.memoizedState, n.updateQueue = e.updateQueue, t = e.dependencies, n.dependencies = t === null ? null : { lanes: t.lanes, firstContext: t.firstContext }, n.sibling = e.sibling, n.index = e.index, n.ref = e.ref, n.refCleanup = e.refCleanup, n;
  }
  function Js(e, t) {
    e.flags &= 65011714;
    var n = e.alternate;
    return n === null ? (e.childLanes = 0, e.lanes = t, e.child = null, e.subtreeFlags = 0, e.memoizedProps = null, e.memoizedState = null, e.updateQueue = null, e.dependencies = null, e.stateNode = null) : (e.childLanes = n.childLanes, e.lanes = n.lanes, e.child = n.child, e.subtreeFlags = 0, e.deletions = null, e.memoizedProps = n.memoizedProps, e.memoizedState = n.memoizedState, e.updateQueue = n.updateQueue, e.type = n.type, t = n.dependencies, e.dependencies = t === null ? null : { lanes: t.lanes, firstContext: t.firstContext }), e;
  }
  function Ma(e, t, n, r, a, l) {
    var i = 0;
    if (r = e, typeof e == "function") hi(e) && (i = 1);
    else if (typeof e == "string") i = gh(e, n, B.current) ? 26 : e === "html" || e === "head" || e === "body" ? 27 : 5;
    else e: switch (e) {
      case L:
        return e = lt(31, n, t, a), e.elementType = L, e.lanes = l, e;
      case W:
        return kn(n.children, a, l, t);
      case fe:
        i = 8, a |= 24;
        break;
      case ae:
        return e = lt(12, n, t, a | 2), e.elementType = ae, e.lanes = l, e;
      case X:
        return e = lt(13, n, t, a), e.elementType = X, e.lanes = l, e;
      case Ne:
        return e = lt(19, n, t, a), e.elementType = Ne, e.lanes = l, e;
      default:
        if (typeof e == "object" && e !== null) switch (e.$$typeof) {
          case we:
          case Ee:
            i = 10;
            break e;
          case Fe:
            i = 9;
            break e;
          case Ae:
            i = 11;
            break e;
          case T:
            i = 14;
            break e;
          case C:
            i = 16, r = null;
            break e;
        }
        i = 29, n = Error(o(130, e === null ? "null" : typeof e, "")), r = null;
    }
    return t = lt(i, n, t, a), t.elementType = e, t.type = r, t.lanes = l, t;
  }
  function kn(e, t, n, r) {
    return e = lt(7, e, r, t), e.lanes = n, e;
  }
  function mi(e, t, n) {
    return e = lt(6, e, null, t), e.lanes = n, e;
  }
  function gi(e, t, n) {
    return t = lt(4, e.children !== null ? e.children : [], e.key, t), t.lanes = n, t.stateNode = { containerInfo: e.containerInfo, pendingChildren: null, implementation: e.implementation }, t;
  }
  var Xn = [], Zn = 0, Ia = null, Ua = 0, ht = [], mt = 0, Sn = null, Tt = 1, At = "";
  function Nn(e, t) {
    Xn[Zn++] = Ua, Xn[Zn++] = Ia, Ia = e, Ua = t;
  }
  function eu(e, t, n) {
    ht[mt++] = Tt, ht[mt++] = At, ht[mt++] = Sn, Sn = e;
    var r = Tt;
    e = At;
    var a = 32 - rt(r) - 1;
    r &= ~(1 << a), n += 1;
    var l = 32 - rt(t) + a;
    if (30 < l) {
      var i = a - a % 5;
      l = (r & (1 << i) - 1).toString(32), r >>= i, a -= i, Tt = 1 << 32 - rt(t) + a | n << a | r, At = l + e;
    } else Tt = 1 << l | n << a | r, At = e;
  }
  function yi(e) {
    e.return !== null && (Nn(e, 1), eu(e, 1, 0));
  }
  function vi(e) {
    for (; e === Ia; ) Ia = Xn[--Zn], Xn[Zn] = null, Ua = Xn[--Zn], Xn[Zn] = null;
    for (; e === Sn; ) Sn = ht[--mt], ht[mt] = null, At = ht[--mt], ht[mt] = null, Tt = ht[--mt], ht[mt] = null;
  }
  var Ye = null, ze = null, de = false, En = null, Nt = false, bi = Error(o(519));
  function _n(e) {
    var t = Error(o(418, ""));
    throw Fr(ft(t, e)), bi;
  }
  function tu(e) {
    var t = e.stateNode, n = e.type, r = e.memoizedProps;
    switch (t[Ke] = e, t[Ge] = r, n) {
      case "dialog":
        re("cancel", t), re("close", t);
        break;
      case "iframe":
      case "object":
      case "embed":
        re("load", t);
        break;
      case "video":
      case "audio":
        for (n = 0; n < ia.length; n++) re(ia[n], t);
        break;
      case "source":
        re("error", t);
        break;
      case "img":
      case "image":
      case "link":
        re("error", t), re("load", t);
        break;
      case "details":
        re("toggle", t);
        break;
      case "input":
        re("invalid", t), gs(t, r.value, r.defaultValue, r.checked, r.defaultChecked, r.type, r.name, true), _a(t);
        break;
      case "select":
        re("invalid", t);
        break;
      case "textarea":
        re("invalid", t), vs(t, r.value, r.defaultValue, r.children), _a(t);
    }
    n = r.children, typeof n != "string" && typeof n != "number" && typeof n != "bigint" || t.textContent === "" + n || r.suppressHydrationWarning === true || vd(t.textContent, n) ? (r.popover != null && (re("beforetoggle", t), re("toggle", t)), r.onScroll != null && re("scroll", t), r.onScrollEnd != null && re("scrollend", t), r.onClick != null && (t.onclick = xl), t = true) : t = false, t || _n(e);
  }
  function nu(e) {
    for (Ye = e.return; Ye; ) switch (Ye.tag) {
      case 5:
      case 13:
        Nt = false;
        return;
      case 27:
      case 3:
        Nt = true;
        return;
      default:
        Ye = Ye.return;
    }
  }
  function Rr(e) {
    if (e !== Ye) return false;
    if (!de) return nu(e), de = true, false;
    var t = e.tag, n;
    if ((n = t !== 3 && t !== 27) && ((n = t === 5) && (n = e.type, n = !(n !== "form" && n !== "button") || Fo(e.type, e.memoizedProps)), n = !n), n && ze && _n(e), nu(e), t === 13) {
      if (e = e.memoizedState, e = e !== null ? e.dehydrated : null, !e) throw Error(o(317));
      e: {
        for (e = e.nextSibling, t = 0; e; ) {
          if (e.nodeType === 8) if (n = e.data, n === "/$") {
            if (t === 0) {
              ze = kt(e.nextSibling);
              break e;
            }
            t--;
          } else n !== "$" && n !== "$!" && n !== "$?" || t++;
          e = e.nextSibling;
        }
        ze = null;
      }
    } else t === 27 ? (t = ze, dn(e.type) ? (e = Uo, Uo = null, ze = e) : ze = t) : ze = Ye ? kt(e.stateNode.nextSibling) : null;
    return true;
  }
  function Or() {
    ze = Ye = null, de = false;
  }
  function ru() {
    var e = En;
    return e !== null && (et === null ? et = e : et.push.apply(et, e), En = null), e;
  }
  function Fr(e) {
    En === null ? En = [e] : En.push(e);
  }
  var xi = j(null), jn = null, Rt = null;
  function Yt(e, t, n) {
    A(xi, t._currentValue), t._currentValue = n;
  }
  function Ot(e) {
    e._currentValue = xi.current, F(xi);
  }
  function wi(e, t, n) {
    for (; e !== null; ) {
      var r = e.alternate;
      if ((e.childLanes & t) !== t ? (e.childLanes |= t, r !== null && (r.childLanes |= t)) : r !== null && (r.childLanes & t) !== t && (r.childLanes |= t), e === n) break;
      e = e.return;
    }
  }
  function ki(e, t, n, r) {
    var a = e.child;
    for (a !== null && (a.return = e); a !== null; ) {
      var l = a.dependencies;
      if (l !== null) {
        var i = a.child;
        l = l.firstContext;
        e: for (; l !== null; ) {
          var u = l;
          l = a;
          for (var d = 0; d < t.length; d++) if (u.context === t[d]) {
            l.lanes |= n, u = l.alternate, u !== null && (u.lanes |= n), wi(l.return, n, e), r || (i = null);
            break e;
          }
          l = u.next;
        }
      } else if (a.tag === 18) {
        if (i = a.return, i === null) throw Error(o(341));
        i.lanes |= n, l = i.alternate, l !== null && (l.lanes |= n), wi(i, n, e), i = null;
      } else i = a.child;
      if (i !== null) i.return = a;
      else for (i = a; i !== null; ) {
        if (i === e) {
          i = null;
          break;
        }
        if (a = i.sibling, a !== null) {
          a.return = i.return, i = a;
          break;
        }
        i = i.return;
      }
      a = i;
    }
  }
  function Dr(e, t, n, r) {
    e = null;
    for (var a = t, l = false; a !== null; ) {
      if (!l) {
        if ((a.flags & 524288) !== 0) l = true;
        else if ((a.flags & 262144) !== 0) break;
      }
      if (a.tag === 10) {
        var i = a.alternate;
        if (i === null) throw Error(o(387));
        if (i = i.memoizedProps, i !== null) {
          var u = a.type;
          at(a.pendingProps.value, i.value) || (e !== null ? e.push(u) : e = [u]);
        }
      } else if (a === tt.current) {
        if (i = a.alternate, i === null) throw Error(o(387));
        i.memoizedState.memoizedState !== a.memoizedState.memoizedState && (e !== null ? e.push(fa) : e = [fa]);
      }
      a = a.return;
    }
    e !== null && ki(t, e, n, r), t.flags |= 262144;
  }
  function Ba(e) {
    for (e = e.firstContext; e !== null; ) {
      if (!at(e.context._currentValue, e.memoizedValue)) return true;
      e = e.next;
    }
    return false;
  }
  function Cn(e) {
    jn = e, Rt = null, e = e.dependencies, e !== null && (e.firstContext = null);
  }
  function Ve(e) {
    return au(jn, e);
  }
  function Wa(e, t) {
    return jn === null && Cn(e), au(e, t);
  }
  function au(e, t) {
    var n = t._currentValue;
    if (t = { context: t, memoizedValue: n, next: null }, Rt === null) {
      if (e === null) throw Error(o(308));
      Rt = t, e.dependencies = { lanes: 0, firstContext: t }, e.flags |= 524288;
    } else Rt = Rt.next = t;
    return n;
  }
  var mp = typeof AbortController < "u" ? AbortController : function() {
    var e = [], t = this.signal = { aborted: false, addEventListener: function(n, r) {
      e.push(r);
    } };
    this.abort = function() {
      t.aborted = true, e.forEach(function(n) {
        return n();
      });
    };
  }, gp = m.unstable_scheduleCallback, yp = m.unstable_NormalPriority, De = { $$typeof: Ee, Consumer: null, Provider: null, _currentValue: null, _currentValue2: null, _threadCount: 0 };
  function Si() {
    return { controller: new mp(), data: /* @__PURE__ */ new Map(), refCount: 0 };
  }
  function Mr(e) {
    e.refCount--, e.refCount === 0 && gp(yp, function() {
      e.controller.abort();
    });
  }
  var Ir = null, Ni = 0, Jn = 0, er = null;
  function vp(e, t) {
    if (Ir === null) {
      var n = Ir = [];
      Ni = 0, Jn = jo(), er = { status: "pending", value: void 0, then: function(r) {
        n.push(r);
      } };
    }
    return Ni++, t.then(lu, lu), t;
  }
  function lu() {
    if (--Ni === 0 && Ir !== null) {
      er !== null && (er.status = "fulfilled");
      var e = Ir;
      Ir = null, Jn = 0, er = null;
      for (var t = 0; t < e.length; t++) (0, e[t])();
    }
  }
  function bp(e, t) {
    var n = [], r = { status: "pending", value: null, reason: null, then: function(a) {
      n.push(a);
    } };
    return e.then(function() {
      r.status = "fulfilled", r.value = t;
      for (var a = 0; a < n.length; a++) (0, n[a])(t);
    }, function(a) {
      for (r.status = "rejected", r.reason = a, a = 0; a < n.length; a++) (0, n[a])(void 0);
    }), r;
  }
  var iu = x.S;
  x.S = function(e, t) {
    typeof t == "object" && t !== null && typeof t.then == "function" && vp(e, t), iu !== null && iu(e, t);
  };
  var zn = j(null);
  function Ei() {
    var e = zn.current;
    return e !== null ? e : Se.pooledCache;
  }
  function Ha(e, t) {
    t === null ? A(zn, zn.current) : A(zn, t.pool);
  }
  function ou() {
    var e = Ei();
    return e === null ? null : { parent: De._currentValue, pool: e };
  }
  var Ur = Error(o(460)), su = Error(o(474)), $a = Error(o(542)), _i = { then: function() {
  } };
  function uu(e) {
    return e = e.status, e === "fulfilled" || e === "rejected";
  }
  function qa() {
  }
  function cu(e, t, n) {
    switch (n = e[n], n === void 0 ? e.push(t) : n !== t && (t.then(qa, qa), t = n), t.status) {
      case "fulfilled":
        return t.value;
      case "rejected":
        throw e = t.reason, fu(e), e;
      default:
        if (typeof t.status == "string") t.then(qa, qa);
        else {
          if (e = Se, e !== null && 100 < e.shellSuspendCounter) throw Error(o(482));
          e = t, e.status = "pending", e.then(function(r) {
            if (t.status === "pending") {
              var a = t;
              a.status = "fulfilled", a.value = r;
            }
          }, function(r) {
            if (t.status === "pending") {
              var a = t;
              a.status = "rejected", a.reason = r;
            }
          });
        }
        switch (t.status) {
          case "fulfilled":
            return t.value;
          case "rejected":
            throw e = t.reason, fu(e), e;
        }
        throw Br = t, Ur;
    }
  }
  var Br = null;
  function du() {
    if (Br === null) throw Error(o(459));
    var e = Br;
    return Br = null, e;
  }
  function fu(e) {
    if (e === Ur || e === $a) throw Error(o(483));
  }
  var Gt = false;
  function ji(e) {
    e.updateQueue = { baseState: e.memoizedState, firstBaseUpdate: null, lastBaseUpdate: null, shared: { pending: null, lanes: 0, hiddenCallbacks: null }, callbacks: null };
  }
  function Ci(e, t) {
    e = e.updateQueue, t.updateQueue === e && (t.updateQueue = { baseState: e.baseState, firstBaseUpdate: e.firstBaseUpdate, lastBaseUpdate: e.lastBaseUpdate, shared: e.shared, callbacks: null });
  }
  function Xt(e) {
    return { lane: e, tag: 0, payload: null, callback: null, next: null };
  }
  function Zt(e, t, n) {
    var r = e.updateQueue;
    if (r === null) return null;
    if (r = r.shared, (he & 2) !== 0) {
      var a = r.pending;
      return a === null ? t.next = t : (t.next = a.next, a.next = t), r.pending = t, t = Da(e), Zs(e, null, n), t;
    }
    return Fa(e, r, t, n), Da(e);
  }
  function Wr(e, t, n) {
    if (t = t.updateQueue, t !== null && (t = t.shared, (n & 4194048) !== 0)) {
      var r = t.lanes;
      r &= e.pendingLanes, n |= r, t.lanes = n, ls(e, n);
    }
  }
  function zi(e, t) {
    var n = e.updateQueue, r = e.alternate;
    if (r !== null && (r = r.updateQueue, n === r)) {
      var a = null, l = null;
      if (n = n.firstBaseUpdate, n !== null) {
        do {
          var i = { lane: n.lane, tag: n.tag, payload: n.payload, callback: null, next: null };
          l === null ? a = l = i : l = l.next = i, n = n.next;
        } while (n !== null);
        l === null ? a = l = t : l = l.next = t;
      } else a = l = t;
      n = { baseState: r.baseState, firstBaseUpdate: a, lastBaseUpdate: l, shared: r.shared, callbacks: r.callbacks }, e.updateQueue = n;
      return;
    }
    e = n.lastBaseUpdate, e === null ? n.firstBaseUpdate = t : e.next = t, n.lastBaseUpdate = t;
  }
  var Pi = false;
  function Hr() {
    if (Pi) {
      var e = er;
      if (e !== null) throw e;
    }
  }
  function $r(e, t, n, r) {
    Pi = false;
    var a = e.updateQueue;
    Gt = false;
    var l = a.firstBaseUpdate, i = a.lastBaseUpdate, u = a.shared.pending;
    if (u !== null) {
      a.shared.pending = null;
      var d = u, y = d.next;
      d.next = null, i === null ? l = y : i.next = y, i = d;
      var w = e.alternate;
      w !== null && (w = w.updateQueue, u = w.lastBaseUpdate, u !== i && (u === null ? w.firstBaseUpdate = y : u.next = y, w.lastBaseUpdate = d));
    }
    if (l !== null) {
      var E = a.baseState;
      i = 0, w = y = d = null, u = l;
      do {
        var v = u.lane & -536870913, b = v !== u.lane;
        if (b ? (le & v) === v : (r & v) === v) {
          v !== 0 && v === Jn && (Pi = true), w !== null && (w = w.next = { lane: 0, tag: u.tag, payload: u.payload, callback: null, next: null });
          e: {
            var Q = e, H = u;
            v = t;
            var ve = n;
            switch (H.tag) {
              case 1:
                if (Q = H.payload, typeof Q == "function") {
                  E = Q.call(ve, E, v);
                  break e;
                }
                E = Q;
                break e;
              case 3:
                Q.flags = Q.flags & -65537 | 128;
              case 0:
                if (Q = H.payload, v = typeof Q == "function" ? Q.call(ve, E, v) : Q, v == null) break e;
                E = R({}, E, v);
                break e;
              case 2:
                Gt = true;
            }
          }
          v = u.callback, v !== null && (e.flags |= 64, b && (e.flags |= 8192), b = a.callbacks, b === null ? a.callbacks = [v] : b.push(v));
        } else b = { lane: v, tag: u.tag, payload: u.payload, callback: u.callback, next: null }, w === null ? (y = w = b, d = E) : w = w.next = b, i |= v;
        if (u = u.next, u === null) {
          if (u = a.shared.pending, u === null) break;
          b = u, u = b.next, b.next = null, a.lastBaseUpdate = b, a.shared.pending = null;
        }
      } while (true);
      w === null && (d = E), a.baseState = d, a.firstBaseUpdate = y, a.lastBaseUpdate = w, l === null && (a.shared.lanes = 0), on |= i, e.lanes = i, e.memoizedState = E;
    }
  }
  function pu(e, t) {
    if (typeof e != "function") throw Error(o(191, e));
    e.call(t);
  }
  function hu(e, t) {
    var n = e.callbacks;
    if (n !== null) for (e.callbacks = null, e = 0; e < n.length; e++) pu(n[e], t);
  }
  var tr = j(null), Ka = j(0);
  function mu(e, t) {
    e = Ht, A(Ka, e), A(tr, t), Ht = e | t.baseLanes;
  }
  function Li() {
    A(Ka, Ht), A(tr, tr.current);
  }
  function Ti() {
    Ht = Ka.current, F(tr), F(Ka);
  }
  var Jt = 0, ee = null, ge = null, Re = null, Va = false, nr = false, Pn = false, Qa = 0, qr = 0, rr = null, xp = 0;
  function Le() {
    throw Error(o(321));
  }
  function Ai(e, t) {
    if (t === null) return false;
    for (var n = 0; n < t.length && n < e.length; n++) if (!at(e[n], t[n])) return false;
    return true;
  }
  function Ri(e, t, n, r, a, l) {
    return Jt = l, ee = t, t.memoizedState = null, t.updateQueue = null, t.lanes = 0, x.H = e === null || e.memoizedState === null ? Zu : Ju, Pn = false, l = n(r, a), Pn = false, nr && (l = yu(t, n, r, a)), gu(e), l;
  }
  function gu(e) {
    x.H = el;
    var t = ge !== null && ge.next !== null;
    if (Jt = 0, Re = ge = ee = null, Va = false, qr = 0, rr = null, t) throw Error(o(300));
    e === null || Ue || (e = e.dependencies, e !== null && Ba(e) && (Ue = true));
  }
  function yu(e, t, n, r) {
    ee = e;
    var a = 0;
    do {
      if (nr && (rr = null), qr = 0, nr = false, 25 <= a) throw Error(o(301));
      if (a += 1, Re = ge = null, e.updateQueue != null) {
        var l = e.updateQueue;
        l.lastEffect = null, l.events = null, l.stores = null, l.memoCache != null && (l.memoCache.index = 0);
      }
      x.H = jp, l = t(n, r);
    } while (nr);
    return l;
  }
  function wp() {
    var e = x.H, t = e.useState()[0];
    return t = typeof t.then == "function" ? Kr(t) : t, e = e.useState()[0], (ge !== null ? ge.memoizedState : null) !== e && (ee.flags |= 1024), t;
  }
  function Oi() {
    var e = Qa !== 0;
    return Qa = 0, e;
  }
  function Fi(e, t, n) {
    t.updateQueue = e.updateQueue, t.flags &= -2053, e.lanes &= ~n;
  }
  function Di(e) {
    if (Va) {
      for (e = e.memoizedState; e !== null; ) {
        var t = e.queue;
        t !== null && (t.pending = null), e = e.next;
      }
      Va = false;
    }
    Jt = 0, Re = ge = ee = null, nr = false, qr = Qa = 0, rr = null;
  }
  function Ze() {
    var e = { memoizedState: null, baseState: null, baseQueue: null, queue: null, next: null };
    return Re === null ? ee.memoizedState = Re = e : Re = Re.next = e, Re;
  }
  function Oe() {
    if (ge === null) {
      var e = ee.alternate;
      e = e !== null ? e.memoizedState : null;
    } else e = ge.next;
    var t = Re === null ? ee.memoizedState : Re.next;
    if (t !== null) Re = t, ge = e;
    else {
      if (e === null) throw ee.alternate === null ? Error(o(467)) : Error(o(310));
      ge = e, e = { memoizedState: ge.memoizedState, baseState: ge.baseState, baseQueue: ge.baseQueue, queue: ge.queue, next: null }, Re === null ? ee.memoizedState = Re = e : Re = Re.next = e;
    }
    return Re;
  }
  function Mi() {
    return { lastEffect: null, events: null, stores: null, memoCache: null };
  }
  function Kr(e) {
    var t = qr;
    return qr += 1, rr === null && (rr = []), e = cu(rr, e, t), t = ee, (Re === null ? t.memoizedState : Re.next) === null && (t = t.alternate, x.H = t === null || t.memoizedState === null ? Zu : Ju), e;
  }
  function Ya(e) {
    if (e !== null && typeof e == "object") {
      if (typeof e.then == "function") return Kr(e);
      if (e.$$typeof === Ee) return Ve(e);
    }
    throw Error(o(438, String(e)));
  }
  function Ii(e) {
    var t = null, n = ee.updateQueue;
    if (n !== null && (t = n.memoCache), t == null) {
      var r = ee.alternate;
      r !== null && (r = r.updateQueue, r !== null && (r = r.memoCache, r != null && (t = { data: r.data.map(function(a) {
        return a.slice();
      }), index: 0 })));
    }
    if (t == null && (t = { data: [], index: 0 }), n === null && (n = Mi(), ee.updateQueue = n), n.memoCache = t, n = t.data[t.index], n === void 0) for (n = t.data[t.index] = Array(e), r = 0; r < e; r++) n[r] = J;
    return t.index++, n;
  }
  function Ft(e, t) {
    return typeof t == "function" ? t(e) : t;
  }
  function Ga(e) {
    var t = Oe();
    return Ui(t, ge, e);
  }
  function Ui(e, t, n) {
    var r = e.queue;
    if (r === null) throw Error(o(311));
    r.lastRenderedReducer = n;
    var a = e.baseQueue, l = r.pending;
    if (l !== null) {
      if (a !== null) {
        var i = a.next;
        a.next = l.next, l.next = i;
      }
      t.baseQueue = a = l, r.pending = null;
    }
    if (l = e.baseState, a === null) e.memoizedState = l;
    else {
      t = a.next;
      var u = i = null, d = null, y = t, w = false;
      do {
        var E = y.lane & -536870913;
        if (E !== y.lane ? (le & E) === E : (Jt & E) === E) {
          var v = y.revertLane;
          if (v === 0) d !== null && (d = d.next = { lane: 0, revertLane: 0, action: y.action, hasEagerState: y.hasEagerState, eagerState: y.eagerState, next: null }), E === Jn && (w = true);
          else if ((Jt & v) === v) {
            y = y.next, v === Jn && (w = true);
            continue;
          } else E = { lane: 0, revertLane: y.revertLane, action: y.action, hasEagerState: y.hasEagerState, eagerState: y.eagerState, next: null }, d === null ? (u = d = E, i = l) : d = d.next = E, ee.lanes |= v, on |= v;
          E = y.action, Pn && n(l, E), l = y.hasEagerState ? y.eagerState : n(l, E);
        } else v = { lane: E, revertLane: y.revertLane, action: y.action, hasEagerState: y.hasEagerState, eagerState: y.eagerState, next: null }, d === null ? (u = d = v, i = l) : d = d.next = v, ee.lanes |= E, on |= E;
        y = y.next;
      } while (y !== null && y !== t);
      if (d === null ? i = l : d.next = u, !at(l, e.memoizedState) && (Ue = true, w && (n = er, n !== null))) throw n;
      e.memoizedState = l, e.baseState = i, e.baseQueue = d, r.lastRenderedState = l;
    }
    return a === null && (r.lanes = 0), [e.memoizedState, r.dispatch];
  }
  function Bi(e) {
    var t = Oe(), n = t.queue;
    if (n === null) throw Error(o(311));
    n.lastRenderedReducer = e;
    var r = n.dispatch, a = n.pending, l = t.memoizedState;
    if (a !== null) {
      n.pending = null;
      var i = a = a.next;
      do
        l = e(l, i.action), i = i.next;
      while (i !== a);
      at(l, t.memoizedState) || (Ue = true), t.memoizedState = l, t.baseQueue === null && (t.baseState = l), n.lastRenderedState = l;
    }
    return [l, r];
  }
  function vu(e, t, n) {
    var r = ee, a = Oe(), l = de;
    if (l) {
      if (n === void 0) throw Error(o(407));
      n = n();
    } else n = t();
    var i = !at((ge || a).memoizedState, n);
    i && (a.memoizedState = n, Ue = true), a = a.queue;
    var u = wu.bind(null, r, a, e);
    if (Vr(2048, 8, u, [e]), a.getSnapshot !== t || i || Re !== null && Re.memoizedState.tag & 1) {
      if (r.flags |= 2048, ar(9, Xa(), xu.bind(null, r, a, n, t), null), Se === null) throw Error(o(349));
      l || (Jt & 124) !== 0 || bu(r, t, n);
    }
    return n;
  }
  function bu(e, t, n) {
    e.flags |= 16384, e = { getSnapshot: t, value: n }, t = ee.updateQueue, t === null ? (t = Mi(), ee.updateQueue = t, t.stores = [e]) : (n = t.stores, n === null ? t.stores = [e] : n.push(e));
  }
  function xu(e, t, n, r) {
    t.value = n, t.getSnapshot = r, ku(t) && Su(e);
  }
  function wu(e, t, n) {
    return n(function() {
      ku(t) && Su(e);
    });
  }
  function ku(e) {
    var t = e.getSnapshot;
    e = e.value;
    try {
      var n = t();
      return !at(e, n);
    } catch {
      return true;
    }
  }
  function Su(e) {
    var t = Yn(e, 2);
    t !== null && ct(t, e, 2);
  }
  function Wi(e) {
    var t = Ze();
    if (typeof e == "function") {
      var n = e;
      if (e = n(), Pn) {
        Kt(true);
        try {
          n();
        } finally {
          Kt(false);
        }
      }
    }
    return t.memoizedState = t.baseState = e, t.queue = { pending: null, lanes: 0, dispatch: null, lastRenderedReducer: Ft, lastRenderedState: e }, t;
  }
  function Nu(e, t, n, r) {
    return e.baseState = n, Ui(e, ge, typeof r == "function" ? r : Ft);
  }
  function kp(e, t, n, r, a) {
    if (Ja(e)) throw Error(o(485));
    if (e = t.action, e !== null) {
      var l = { payload: a, action: e, next: null, isTransition: true, status: "pending", value: null, reason: null, listeners: [], then: function(i) {
        l.listeners.push(i);
      } };
      x.T !== null ? n(true) : l.isTransition = false, r(l), n = t.pending, n === null ? (l.next = t.pending = l, Eu(t, l)) : (l.next = n.next, t.pending = n.next = l);
    }
  }
  function Eu(e, t) {
    var n = t.action, r = t.payload, a = e.state;
    if (t.isTransition) {
      var l = x.T, i = {};
      x.T = i;
      try {
        var u = n(a, r), d = x.S;
        d !== null && d(i, u), _u(e, t, u);
      } catch (y) {
        Hi(e, t, y);
      } finally {
        x.T = l;
      }
    } else try {
      l = n(a, r), _u(e, t, l);
    } catch (y) {
      Hi(e, t, y);
    }
  }
  function _u(e, t, n) {
    n !== null && typeof n == "object" && typeof n.then == "function" ? n.then(function(r) {
      ju(e, t, r);
    }, function(r) {
      return Hi(e, t, r);
    }) : ju(e, t, n);
  }
  function ju(e, t, n) {
    t.status = "fulfilled", t.value = n, Cu(t), e.state = n, t = e.pending, t !== null && (n = t.next, n === t ? e.pending = null : (n = n.next, t.next = n, Eu(e, n)));
  }
  function Hi(e, t, n) {
    var r = e.pending;
    if (e.pending = null, r !== null) {
      r = r.next;
      do
        t.status = "rejected", t.reason = n, Cu(t), t = t.next;
      while (t !== r);
    }
    e.action = null;
  }
  function Cu(e) {
    e = e.listeners;
    for (var t = 0; t < e.length; t++) (0, e[t])();
  }
  function zu(e, t) {
    return t;
  }
  function Pu(e, t) {
    if (de) {
      var n = Se.formState;
      if (n !== null) {
        e: {
          var r = ee;
          if (de) {
            if (ze) {
              t: {
                for (var a = ze, l = Nt; a.nodeType !== 8; ) {
                  if (!l) {
                    a = null;
                    break t;
                  }
                  if (a = kt(a.nextSibling), a === null) {
                    a = null;
                    break t;
                  }
                }
                l = a.data, a = l === "F!" || l === "F" ? a : null;
              }
              if (a) {
                ze = kt(a.nextSibling), r = a.data === "F!";
                break e;
              }
            }
            _n(r);
          }
          r = false;
        }
        r && (t = n[0]);
      }
    }
    return n = Ze(), n.memoizedState = n.baseState = t, r = { pending: null, lanes: 0, dispatch: null, lastRenderedReducer: zu, lastRenderedState: t }, n.queue = r, n = Yu.bind(null, ee, r), r.dispatch = n, r = Wi(false), l = Qi.bind(null, ee, false, r.queue), r = Ze(), a = { state: t, dispatch: null, action: e, pending: null }, r.queue = a, n = kp.bind(null, ee, a, l, n), a.dispatch = n, r.memoizedState = e, [t, n, false];
  }
  function Lu(e) {
    var t = Oe();
    return Tu(t, ge, e);
  }
  function Tu(e, t, n) {
    if (t = Ui(e, t, zu)[0], e = Ga(Ft)[0], typeof t == "object" && t !== null && typeof t.then == "function") try {
      var r = Kr(t);
    } catch (i) {
      throw i === Ur ? $a : i;
    }
    else r = t;
    t = Oe();
    var a = t.queue, l = a.dispatch;
    return n !== t.memoizedState && (ee.flags |= 2048, ar(9, Xa(), Sp.bind(null, a, n), null)), [r, l, e];
  }
  function Sp(e, t) {
    e.action = t;
  }
  function Au(e) {
    var t = Oe(), n = ge;
    if (n !== null) return Tu(t, n, e);
    Oe(), t = t.memoizedState, n = Oe();
    var r = n.queue.dispatch;
    return n.memoizedState = e, [t, r, false];
  }
  function ar(e, t, n, r) {
    return e = { tag: e, create: n, deps: r, inst: t, next: null }, t = ee.updateQueue, t === null && (t = Mi(), ee.updateQueue = t), n = t.lastEffect, n === null ? t.lastEffect = e.next = e : (r = n.next, n.next = e, e.next = r, t.lastEffect = e), e;
  }
  function Xa() {
    return { destroy: void 0, resource: void 0 };
  }
  function Ru() {
    return Oe().memoizedState;
  }
  function Za(e, t, n, r) {
    var a = Ze();
    r = r === void 0 ? null : r, ee.flags |= e, a.memoizedState = ar(1 | t, Xa(), n, r);
  }
  function Vr(e, t, n, r) {
    var a = Oe();
    r = r === void 0 ? null : r;
    var l = a.memoizedState.inst;
    ge !== null && r !== null && Ai(r, ge.memoizedState.deps) ? a.memoizedState = ar(t, l, n, r) : (ee.flags |= e, a.memoizedState = ar(1 | t, l, n, r));
  }
  function Ou(e, t) {
    Za(8390656, 8, e, t);
  }
  function Fu(e, t) {
    Vr(2048, 8, e, t);
  }
  function Du(e, t) {
    return Vr(4, 2, e, t);
  }
  function Mu(e, t) {
    return Vr(4, 4, e, t);
  }
  function Iu(e, t) {
    if (typeof t == "function") {
      e = e();
      var n = t(e);
      return function() {
        typeof n == "function" ? n() : t(null);
      };
    }
    if (t != null) return e = e(), t.current = e, function() {
      t.current = null;
    };
  }
  function Uu(e, t, n) {
    n = n != null ? n.concat([e]) : null, Vr(4, 4, Iu.bind(null, t, e), n);
  }
  function $i() {
  }
  function Bu(e, t) {
    var n = Oe();
    t = t === void 0 ? null : t;
    var r = n.memoizedState;
    return t !== null && Ai(t, r[1]) ? r[0] : (n.memoizedState = [e, t], e);
  }
  function Wu(e, t) {
    var n = Oe();
    t = t === void 0 ? null : t;
    var r = n.memoizedState;
    if (t !== null && Ai(t, r[1])) return r[0];
    if (r = e(), Pn) {
      Kt(true);
      try {
        e();
      } finally {
        Kt(false);
      }
    }
    return n.memoizedState = [r, t], r;
  }
  function qi(e, t, n) {
    return n === void 0 || (Jt & 1073741824) !== 0 ? e.memoizedState = t : (e.memoizedState = n, e = qc(), ee.lanes |= e, on |= e, n);
  }
  function Hu(e, t, n, r) {
    return at(n, t) ? n : tr.current !== null ? (e = qi(e, n, r), at(e, t) || (Ue = true), e) : (Jt & 42) === 0 ? (Ue = true, e.memoizedState = n) : (e = qc(), ee.lanes |= e, on |= e, t);
  }
  function $u(e, t, n, r, a) {
    var l = z.p;
    z.p = l !== 0 && 8 > l ? l : 8;
    var i = x.T, u = {};
    x.T = u, Qi(e, false, t, n);
    try {
      var d = a(), y = x.S;
      if (y !== null && y(u, d), d !== null && typeof d == "object" && typeof d.then == "function") {
        var w = bp(d, r);
        Qr(e, t, w, ut(e));
      } else Qr(e, t, r, ut(e));
    } catch (E) {
      Qr(e, t, { then: function() {
      }, status: "rejected", reason: E }, ut());
    } finally {
      z.p = l, x.T = i;
    }
  }
  function Np() {
  }
  function Ki(e, t, n, r) {
    if (e.tag !== 5) throw Error(o(476));
    var a = qu(e).queue;
    $u(e, a, t, q, n === null ? Np : function() {
      return Ku(e), n(r);
    });
  }
  function qu(e) {
    var t = e.memoizedState;
    if (t !== null) return t;
    t = { memoizedState: q, baseState: q, baseQueue: null, queue: { pending: null, lanes: 0, dispatch: null, lastRenderedReducer: Ft, lastRenderedState: q }, next: null };
    var n = {};
    return t.next = { memoizedState: n, baseState: n, baseQueue: null, queue: { pending: null, lanes: 0, dispatch: null, lastRenderedReducer: Ft, lastRenderedState: n }, next: null }, e.memoizedState = t, e = e.alternate, e !== null && (e.memoizedState = t), t;
  }
  function Ku(e) {
    var t = qu(e).next.queue;
    Qr(e, t, {}, ut());
  }
  function Vi() {
    return Ve(fa);
  }
  function Vu() {
    return Oe().memoizedState;
  }
  function Qu() {
    return Oe().memoizedState;
  }
  function Ep(e) {
    for (var t = e.return; t !== null; ) {
      switch (t.tag) {
        case 24:
        case 3:
          var n = ut();
          e = Xt(n);
          var r = Zt(t, e, n);
          r !== null && (ct(r, t, n), Wr(r, t, n)), t = { cache: Si() }, e.payload = t;
          return;
      }
      t = t.return;
    }
  }
  function _p(e, t, n) {
    var r = ut();
    n = { lane: r, revertLane: 0, action: n, hasEagerState: false, eagerState: null, next: null }, Ja(e) ? Gu(t, n) : (n = pi(e, t, n, r), n !== null && (ct(n, e, r), Xu(n, t, r)));
  }
  function Yu(e, t, n) {
    var r = ut();
    Qr(e, t, n, r);
  }
  function Qr(e, t, n, r) {
    var a = { lane: r, revertLane: 0, action: n, hasEagerState: false, eagerState: null, next: null };
    if (Ja(e)) Gu(t, a);
    else {
      var l = e.alternate;
      if (e.lanes === 0 && (l === null || l.lanes === 0) && (l = t.lastRenderedReducer, l !== null)) try {
        var i = t.lastRenderedState, u = l(i, n);
        if (a.hasEagerState = true, a.eagerState = u, at(u, i)) return Fa(e, t, a, 0), Se === null && Oa(), false;
      } catch {
      } finally {
      }
      if (n = pi(e, t, a, r), n !== null) return ct(n, e, r), Xu(n, t, r), true;
    }
    return false;
  }
  function Qi(e, t, n, r) {
    if (r = { lane: 2, revertLane: jo(), action: r, hasEagerState: false, eagerState: null, next: null }, Ja(e)) {
      if (t) throw Error(o(479));
    } else t = pi(e, n, r, 2), t !== null && ct(t, e, 2);
  }
  function Ja(e) {
    var t = e.alternate;
    return e === ee || t !== null && t === ee;
  }
  function Gu(e, t) {
    nr = Va = true;
    var n = e.pending;
    n === null ? t.next = t : (t.next = n.next, n.next = t), e.pending = t;
  }
  function Xu(e, t, n) {
    if ((n & 4194048) !== 0) {
      var r = t.lanes;
      r &= e.pendingLanes, n |= r, t.lanes = n, ls(e, n);
    }
  }
  var el = { readContext: Ve, use: Ya, useCallback: Le, useContext: Le, useEffect: Le, useImperativeHandle: Le, useLayoutEffect: Le, useInsertionEffect: Le, useMemo: Le, useReducer: Le, useRef: Le, useState: Le, useDebugValue: Le, useDeferredValue: Le, useTransition: Le, useSyncExternalStore: Le, useId: Le, useHostTransitionStatus: Le, useFormState: Le, useActionState: Le, useOptimistic: Le, useMemoCache: Le, useCacheRefresh: Le }, Zu = { readContext: Ve, use: Ya, useCallback: function(e, t) {
    return Ze().memoizedState = [e, t === void 0 ? null : t], e;
  }, useContext: Ve, useEffect: Ou, useImperativeHandle: function(e, t, n) {
    n = n != null ? n.concat([e]) : null, Za(4194308, 4, Iu.bind(null, t, e), n);
  }, useLayoutEffect: function(e, t) {
    return Za(4194308, 4, e, t);
  }, useInsertionEffect: function(e, t) {
    Za(4, 2, e, t);
  }, useMemo: function(e, t) {
    var n = Ze();
    t = t === void 0 ? null : t;
    var r = e();
    if (Pn) {
      Kt(true);
      try {
        e();
      } finally {
        Kt(false);
      }
    }
    return n.memoizedState = [r, t], r;
  }, useReducer: function(e, t, n) {
    var r = Ze();
    if (n !== void 0) {
      var a = n(t);
      if (Pn) {
        Kt(true);
        try {
          n(t);
        } finally {
          Kt(false);
        }
      }
    } else a = t;
    return r.memoizedState = r.baseState = a, e = { pending: null, lanes: 0, dispatch: null, lastRenderedReducer: e, lastRenderedState: a }, r.queue = e, e = e.dispatch = _p.bind(null, ee, e), [r.memoizedState, e];
  }, useRef: function(e) {
    var t = Ze();
    return e = { current: e }, t.memoizedState = e;
  }, useState: function(e) {
    e = Wi(e);
    var t = e.queue, n = Yu.bind(null, ee, t);
    return t.dispatch = n, [e.memoizedState, n];
  }, useDebugValue: $i, useDeferredValue: function(e, t) {
    var n = Ze();
    return qi(n, e, t);
  }, useTransition: function() {
    var e = Wi(false);
    return e = $u.bind(null, ee, e.queue, true, false), Ze().memoizedState = e, [false, e];
  }, useSyncExternalStore: function(e, t, n) {
    var r = ee, a = Ze();
    if (de) {
      if (n === void 0) throw Error(o(407));
      n = n();
    } else {
      if (n = t(), Se === null) throw Error(o(349));
      (le & 124) !== 0 || bu(r, t, n);
    }
    a.memoizedState = n;
    var l = { value: n, getSnapshot: t };
    return a.queue = l, Ou(wu.bind(null, r, l, e), [e]), r.flags |= 2048, ar(9, Xa(), xu.bind(null, r, l, n, t), null), n;
  }, useId: function() {
    var e = Ze(), t = Se.identifierPrefix;
    if (de) {
      var n = At, r = Tt;
      n = (r & ~(1 << 32 - rt(r) - 1)).toString(32) + n, t = "«" + t + "R" + n, n = Qa++, 0 < n && (t += "H" + n.toString(32)), t += "»";
    } else n = xp++, t = "«" + t + "r" + n.toString(32) + "»";
    return e.memoizedState = t;
  }, useHostTransitionStatus: Vi, useFormState: Pu, useActionState: Pu, useOptimistic: function(e) {
    var t = Ze();
    t.memoizedState = t.baseState = e;
    var n = { pending: null, lanes: 0, dispatch: null, lastRenderedReducer: null, lastRenderedState: null };
    return t.queue = n, t = Qi.bind(null, ee, true, n), n.dispatch = t, [e, t];
  }, useMemoCache: Ii, useCacheRefresh: function() {
    return Ze().memoizedState = Ep.bind(null, ee);
  } }, Ju = { readContext: Ve, use: Ya, useCallback: Bu, useContext: Ve, useEffect: Fu, useImperativeHandle: Uu, useInsertionEffect: Du, useLayoutEffect: Mu, useMemo: Wu, useReducer: Ga, useRef: Ru, useState: function() {
    return Ga(Ft);
  }, useDebugValue: $i, useDeferredValue: function(e, t) {
    var n = Oe();
    return Hu(n, ge.memoizedState, e, t);
  }, useTransition: function() {
    var e = Ga(Ft)[0], t = Oe().memoizedState;
    return [typeof e == "boolean" ? e : Kr(e), t];
  }, useSyncExternalStore: vu, useId: Vu, useHostTransitionStatus: Vi, useFormState: Lu, useActionState: Lu, useOptimistic: function(e, t) {
    var n = Oe();
    return Nu(n, ge, e, t);
  }, useMemoCache: Ii, useCacheRefresh: Qu }, jp = { readContext: Ve, use: Ya, useCallback: Bu, useContext: Ve, useEffect: Fu, useImperativeHandle: Uu, useInsertionEffect: Du, useLayoutEffect: Mu, useMemo: Wu, useReducer: Bi, useRef: Ru, useState: function() {
    return Bi(Ft);
  }, useDebugValue: $i, useDeferredValue: function(e, t) {
    var n = Oe();
    return ge === null ? qi(n, e, t) : Hu(n, ge.memoizedState, e, t);
  }, useTransition: function() {
    var e = Bi(Ft)[0], t = Oe().memoizedState;
    return [typeof e == "boolean" ? e : Kr(e), t];
  }, useSyncExternalStore: vu, useId: Vu, useHostTransitionStatus: Vi, useFormState: Au, useActionState: Au, useOptimistic: function(e, t) {
    var n = Oe();
    return ge !== null ? Nu(n, ge, e, t) : (n.baseState = e, [e, n.queue.dispatch]);
  }, useMemoCache: Ii, useCacheRefresh: Qu }, lr = null, Yr = 0;
  function tl(e) {
    var t = Yr;
    return Yr += 1, lr === null && (lr = []), cu(lr, e, t);
  }
  function Gr(e, t) {
    t = t.props.ref, e.ref = t !== void 0 ? t : null;
  }
  function nl(e, t) {
    throw t.$$typeof === K ? Error(o(525)) : (e = Object.prototype.toString.call(t), Error(o(31, e === "[object Object]" ? "object with keys {" + Object.keys(t).join(", ") + "}" : e)));
  }
  function ec(e) {
    var t = e._init;
    return t(e._payload);
  }
  function tc(e) {
    function t(h, p) {
      if (e) {
        var g = h.deletions;
        g === null ? (h.deletions = [p], h.flags |= 16) : g.push(p);
      }
    }
    function n(h, p) {
      if (!e) return null;
      for (; p !== null; ) t(h, p), p = p.sibling;
      return null;
    }
    function r(h) {
      for (var p = /* @__PURE__ */ new Map(); h !== null; ) h.key !== null ? p.set(h.key, h) : p.set(h.index, h), h = h.sibling;
      return p;
    }
    function a(h, p) {
      return h = Lt(h, p), h.index = 0, h.sibling = null, h;
    }
    function l(h, p, g) {
      return h.index = g, e ? (g = h.alternate, g !== null ? (g = g.index, g < p ? (h.flags |= 67108866, p) : g) : (h.flags |= 67108866, p)) : (h.flags |= 1048576, p);
    }
    function i(h) {
      return e && h.alternate === null && (h.flags |= 67108866), h;
    }
    function u(h, p, g, S) {
      return p === null || p.tag !== 6 ? (p = mi(g, h.mode, S), p.return = h, p) : (p = a(p, g), p.return = h, p);
    }
    function d(h, p, g, S) {
      var M = g.type;
      return M === W ? w(h, p, g.props.children, S, g.key) : p !== null && (p.elementType === M || typeof M == "object" && M !== null && M.$$typeof === C && ec(M) === p.type) ? (p = a(p, g.props), Gr(p, g), p.return = h, p) : (p = Ma(g.type, g.key, g.props, null, h.mode, S), Gr(p, g), p.return = h, p);
    }
    function y(h, p, g, S) {
      return p === null || p.tag !== 4 || p.stateNode.containerInfo !== g.containerInfo || p.stateNode.implementation !== g.implementation ? (p = gi(g, h.mode, S), p.return = h, p) : (p = a(p, g.children || []), p.return = h, p);
    }
    function w(h, p, g, S, M) {
      return p === null || p.tag !== 7 ? (p = kn(g, h.mode, S, M), p.return = h, p) : (p = a(p, g), p.return = h, p);
    }
    function E(h, p, g) {
      if (typeof p == "string" && p !== "" || typeof p == "number" || typeof p == "bigint") return p = mi("" + p, h.mode, g), p.return = h, p;
      if (typeof p == "object" && p !== null) {
        switch (p.$$typeof) {
          case O:
            return g = Ma(p.type, p.key, p.props, null, h.mode, g), Gr(g, p), g.return = h, g;
          case V:
            return p = gi(p, h.mode, g), p.return = h, p;
          case C:
            var S = p._init;
            return p = S(p._payload), E(h, p, g);
        }
        if (oe(p) || ke(p)) return p = kn(p, h.mode, g, null), p.return = h, p;
        if (typeof p.then == "function") return E(h, tl(p), g);
        if (p.$$typeof === Ee) return E(h, Wa(h, p), g);
        nl(h, p);
      }
      return null;
    }
    function v(h, p, g, S) {
      var M = p !== null ? p.key : null;
      if (typeof g == "string" && g !== "" || typeof g == "number" || typeof g == "bigint") return M !== null ? null : u(h, p, "" + g, S);
      if (typeof g == "object" && g !== null) {
        switch (g.$$typeof) {
          case O:
            return g.key === M ? d(h, p, g, S) : null;
          case V:
            return g.key === M ? y(h, p, g, S) : null;
          case C:
            return M = g._init, g = M(g._payload), v(h, p, g, S);
        }
        if (oe(g) || ke(g)) return M !== null ? null : w(h, p, g, S, null);
        if (typeof g.then == "function") return v(h, p, tl(g), S);
        if (g.$$typeof === Ee) return v(h, p, Wa(h, g), S);
        nl(h, g);
      }
      return null;
    }
    function b(h, p, g, S, M) {
      if (typeof S == "string" && S !== "" || typeof S == "number" || typeof S == "bigint") return h = h.get(g) || null, u(p, h, "" + S, M);
      if (typeof S == "object" && S !== null) {
        switch (S.$$typeof) {
          case O:
            return h = h.get(S.key === null ? g : S.key) || null, d(p, h, S, M);
          case V:
            return h = h.get(S.key === null ? g : S.key) || null, y(p, h, S, M);
          case C:
            var te = S._init;
            return S = te(S._payload), b(h, p, g, S, M);
        }
        if (oe(S) || ke(S)) return h = h.get(g) || null, w(p, h, S, M, null);
        if (typeof S.then == "function") return b(h, p, g, tl(S), M);
        if (S.$$typeof === Ee) return b(h, p, g, Wa(p, S), M);
        nl(p, S);
      }
      return null;
    }
    function Q(h, p, g, S) {
      for (var M = null, te = null, I = p, $ = p = 0, We = null; I !== null && $ < g.length; $++) {
        I.index > $ ? (We = I, I = null) : We = I.sibling;
        var ce = v(h, I, g[$], S);
        if (ce === null) {
          I === null && (I = We);
          break;
        }
        e && I && ce.alternate === null && t(h, I), p = l(ce, p, $), te === null ? M = ce : te.sibling = ce, te = ce, I = We;
      }
      if ($ === g.length) return n(h, I), de && Nn(h, $), M;
      if (I === null) {
        for (; $ < g.length; $++) I = E(h, g[$], S), I !== null && (p = l(I, p, $), te === null ? M = I : te.sibling = I, te = I);
        return de && Nn(h, $), M;
      }
      for (I = r(I); $ < g.length; $++) We = b(I, h, $, g[$], S), We !== null && (e && We.alternate !== null && I.delete(We.key === null ? $ : We.key), p = l(We, p, $), te === null ? M = We : te.sibling = We, te = We);
      return e && I.forEach(function(gn) {
        return t(h, gn);
      }), de && Nn(h, $), M;
    }
    function H(h, p, g, S) {
      if (g == null) throw Error(o(151));
      for (var M = null, te = null, I = p, $ = p = 0, We = null, ce = g.next(); I !== null && !ce.done; $++, ce = g.next()) {
        I.index > $ ? (We = I, I = null) : We = I.sibling;
        var gn = v(h, I, ce.value, S);
        if (gn === null) {
          I === null && (I = We);
          break;
        }
        e && I && gn.alternate === null && t(h, I), p = l(gn, p, $), te === null ? M = gn : te.sibling = gn, te = gn, I = We;
      }
      if (ce.done) return n(h, I), de && Nn(h, $), M;
      if (I === null) {
        for (; !ce.done; $++, ce = g.next()) ce = E(h, ce.value, S), ce !== null && (p = l(ce, p, $), te === null ? M = ce : te.sibling = ce, te = ce);
        return de && Nn(h, $), M;
      }
      for (I = r(I); !ce.done; $++, ce = g.next()) ce = b(I, h, $, ce.value, S), ce !== null && (e && ce.alternate !== null && I.delete(ce.key === null ? $ : ce.key), p = l(ce, p, $), te === null ? M = ce : te.sibling = ce, te = ce);
      return e && I.forEach(function(Ch) {
        return t(h, Ch);
      }), de && Nn(h, $), M;
    }
    function ve(h, p, g, S) {
      if (typeof g == "object" && g !== null && g.type === W && g.key === null && (g = g.props.children), typeof g == "object" && g !== null) {
        switch (g.$$typeof) {
          case O:
            e: {
              for (var M = g.key; p !== null; ) {
                if (p.key === M) {
                  if (M = g.type, M === W) {
                    if (p.tag === 7) {
                      n(h, p.sibling), S = a(p, g.props.children), S.return = h, h = S;
                      break e;
                    }
                  } else if (p.elementType === M || typeof M == "object" && M !== null && M.$$typeof === C && ec(M) === p.type) {
                    n(h, p.sibling), S = a(p, g.props), Gr(S, g), S.return = h, h = S;
                    break e;
                  }
                  n(h, p);
                  break;
                } else t(h, p);
                p = p.sibling;
              }
              g.type === W ? (S = kn(g.props.children, h.mode, S, g.key), S.return = h, h = S) : (S = Ma(g.type, g.key, g.props, null, h.mode, S), Gr(S, g), S.return = h, h = S);
            }
            return i(h);
          case V:
            e: {
              for (M = g.key; p !== null; ) {
                if (p.key === M) if (p.tag === 4 && p.stateNode.containerInfo === g.containerInfo && p.stateNode.implementation === g.implementation) {
                  n(h, p.sibling), S = a(p, g.children || []), S.return = h, h = S;
                  break e;
                } else {
                  n(h, p);
                  break;
                }
                else t(h, p);
                p = p.sibling;
              }
              S = gi(g, h.mode, S), S.return = h, h = S;
            }
            return i(h);
          case C:
            return M = g._init, g = M(g._payload), ve(h, p, g, S);
        }
        if (oe(g)) return Q(h, p, g, S);
        if (ke(g)) {
          if (M = ke(g), typeof M != "function") throw Error(o(150));
          return g = M.call(g), H(h, p, g, S);
        }
        if (typeof g.then == "function") return ve(h, p, tl(g), S);
        if (g.$$typeof === Ee) return ve(h, p, Wa(h, g), S);
        nl(h, g);
      }
      return typeof g == "string" && g !== "" || typeof g == "number" || typeof g == "bigint" ? (g = "" + g, p !== null && p.tag === 6 ? (n(h, p.sibling), S = a(p, g), S.return = h, h = S) : (n(h, p), S = mi(g, h.mode, S), S.return = h, h = S), i(h)) : n(h, p);
    }
    return function(h, p, g, S) {
      try {
        Yr = 0;
        var M = ve(h, p, g, S);
        return lr = null, M;
      } catch (I) {
        if (I === Ur || I === $a) throw I;
        var te = lt(29, I, null, h.mode);
        return te.lanes = S, te.return = h, te;
      } finally {
      }
    };
  }
  var ir = tc(true), nc = tc(false), gt = j(null), Dt = null;
  function en(e) {
    var t = e.alternate;
    A(Me, Me.current & 1), A(gt, e), Dt === null && (t === null || tr.current !== null || t.memoizedState !== null) && (Dt = e);
  }
  function rc(e) {
    if (e.tag === 22) {
      if (A(Me, Me.current), A(gt, e), Dt === null) {
        var t = e.alternate;
        t !== null && t.memoizedState !== null && (Dt = e);
      }
    } else tn();
  }
  function tn() {
    A(Me, Me.current), A(gt, gt.current);
  }
  function Mt(e) {
    F(gt), Dt === e && (Dt = null), F(Me);
  }
  var Me = j(0);
  function rl(e) {
    for (var t = e; t !== null; ) {
      if (t.tag === 13) {
        var n = t.memoizedState;
        if (n !== null && (n = n.dehydrated, n === null || n.data === "$?" || Io(n))) return t;
      } else if (t.tag === 19 && t.memoizedProps.revealOrder !== void 0) {
        if ((t.flags & 128) !== 0) return t;
      } else if (t.child !== null) {
        t.child.return = t, t = t.child;
        continue;
      }
      if (t === e) break;
      for (; t.sibling === null; ) {
        if (t.return === null || t.return === e) return null;
        t = t.return;
      }
      t.sibling.return = t.return, t = t.sibling;
    }
    return null;
  }
  function Yi(e, t, n, r) {
    t = e.memoizedState, n = n(r, t), n = n == null ? t : R({}, t, n), e.memoizedState = n, e.lanes === 0 && (e.updateQueue.baseState = n);
  }
  var Gi = { enqueueSetState: function(e, t, n) {
    e = e._reactInternals;
    var r = ut(), a = Xt(r);
    a.payload = t, n != null && (a.callback = n), t = Zt(e, a, r), t !== null && (ct(t, e, r), Wr(t, e, r));
  }, enqueueReplaceState: function(e, t, n) {
    e = e._reactInternals;
    var r = ut(), a = Xt(r);
    a.tag = 1, a.payload = t, n != null && (a.callback = n), t = Zt(e, a, r), t !== null && (ct(t, e, r), Wr(t, e, r));
  }, enqueueForceUpdate: function(e, t) {
    e = e._reactInternals;
    var n = ut(), r = Xt(n);
    r.tag = 2, t != null && (r.callback = t), t = Zt(e, r, n), t !== null && (ct(t, e, n), Wr(t, e, n));
  } };
  function ac(e, t, n, r, a, l, i) {
    return e = e.stateNode, typeof e.shouldComponentUpdate == "function" ? e.shouldComponentUpdate(r, l, i) : t.prototype && t.prototype.isPureReactComponent ? !Tr(n, r) || !Tr(a, l) : true;
  }
  function lc(e, t, n, r) {
    e = t.state, typeof t.componentWillReceiveProps == "function" && t.componentWillReceiveProps(n, r), typeof t.UNSAFE_componentWillReceiveProps == "function" && t.UNSAFE_componentWillReceiveProps(n, r), t.state !== e && Gi.enqueueReplaceState(t, t.state, null);
  }
  function Ln(e, t) {
    var n = t;
    if ("ref" in t) {
      n = {};
      for (var r in t) r !== "ref" && (n[r] = t[r]);
    }
    if (e = e.defaultProps) {
      n === t && (n = R({}, n));
      for (var a in e) n[a] === void 0 && (n[a] = e[a]);
    }
    return n;
  }
  var al = typeof reportError == "function" ? reportError : function(e) {
    if (typeof window == "object" && typeof window.ErrorEvent == "function") {
      var t = new window.ErrorEvent("error", { bubbles: true, cancelable: true, message: typeof e == "object" && e !== null && typeof e.message == "string" ? String(e.message) : String(e), error: e });
      if (!window.dispatchEvent(t)) return;
    } else if (typeof process == "object" && typeof process.emit == "function") {
      process.emit("uncaughtException", e);
      return;
    }
    console.error(e);
  };
  function ic(e) {
    al(e);
  }
  function oc(e) {
    console.error(e);
  }
  function sc(e) {
    al(e);
  }
  function ll(e, t) {
    try {
      var n = e.onUncaughtError;
      n(t.value, { componentStack: t.stack });
    } catch (r) {
      setTimeout(function() {
        throw r;
      });
    }
  }
  function uc(e, t, n) {
    try {
      var r = e.onCaughtError;
      r(n.value, { componentStack: n.stack, errorBoundary: t.tag === 1 ? t.stateNode : null });
    } catch (a) {
      setTimeout(function() {
        throw a;
      });
    }
  }
  function Xi(e, t, n) {
    return n = Xt(n), n.tag = 3, n.payload = { element: null }, n.callback = function() {
      ll(e, t);
    }, n;
  }
  function cc(e) {
    return e = Xt(e), e.tag = 3, e;
  }
  function dc(e, t, n, r) {
    var a = n.type.getDerivedStateFromError;
    if (typeof a == "function") {
      var l = r.value;
      e.payload = function() {
        return a(l);
      }, e.callback = function() {
        uc(t, n, r);
      };
    }
    var i = n.stateNode;
    i !== null && typeof i.componentDidCatch == "function" && (e.callback = function() {
      uc(t, n, r), typeof a != "function" && (sn === null ? sn = /* @__PURE__ */ new Set([this]) : sn.add(this));
      var u = r.stack;
      this.componentDidCatch(r.value, { componentStack: u !== null ? u : "" });
    });
  }
  function Cp(e, t, n, r, a) {
    if (n.flags |= 32768, r !== null && typeof r == "object" && typeof r.then == "function") {
      if (t = n.alternate, t !== null && Dr(t, n, a, true), n = gt.current, n !== null) {
        switch (n.tag) {
          case 13:
            return Dt === null ? ko() : n.alternate === null && Pe === 0 && (Pe = 3), n.flags &= -257, n.flags |= 65536, n.lanes = a, r === _i ? n.flags |= 16384 : (t = n.updateQueue, t === null ? n.updateQueue = /* @__PURE__ */ new Set([r]) : t.add(r), No(e, r, a)), false;
          case 22:
            return n.flags |= 65536, r === _i ? n.flags |= 16384 : (t = n.updateQueue, t === null ? (t = { transitions: null, markerInstances: null, retryQueue: /* @__PURE__ */ new Set([r]) }, n.updateQueue = t) : (n = t.retryQueue, n === null ? t.retryQueue = /* @__PURE__ */ new Set([r]) : n.add(r)), No(e, r, a)), false;
        }
        throw Error(o(435, n.tag));
      }
      return No(e, r, a), ko(), false;
    }
    if (de) return t = gt.current, t !== null ? ((t.flags & 65536) === 0 && (t.flags |= 256), t.flags |= 65536, t.lanes = a, r !== bi && (e = Error(o(422), { cause: r }), Fr(ft(e, n)))) : (r !== bi && (t = Error(o(423), { cause: r }), Fr(ft(t, n))), e = e.current.alternate, e.flags |= 65536, a &= -a, e.lanes |= a, r = ft(r, n), a = Xi(e.stateNode, r, a), zi(e, a), Pe !== 4 && (Pe = 2)), false;
    var l = Error(o(520), { cause: r });
    if (l = ft(l, n), ra === null ? ra = [l] : ra.push(l), Pe !== 4 && (Pe = 2), t === null) return true;
    r = ft(r, n), n = t;
    do {
      switch (n.tag) {
        case 3:
          return n.flags |= 65536, e = a & -a, n.lanes |= e, e = Xi(n.stateNode, r, e), zi(n, e), false;
        case 1:
          if (t = n.type, l = n.stateNode, (n.flags & 128) === 0 && (typeof t.getDerivedStateFromError == "function" || l !== null && typeof l.componentDidCatch == "function" && (sn === null || !sn.has(l)))) return n.flags |= 65536, a &= -a, n.lanes |= a, a = cc(a), dc(a, e, n, r), zi(n, a), false;
      }
      n = n.return;
    } while (n !== null);
    return false;
  }
  var fc = Error(o(461)), Ue = false;
  function He(e, t, n, r) {
    t.child = e === null ? nc(t, null, n, r) : ir(t, e.child, n, r);
  }
  function pc(e, t, n, r, a) {
    n = n.render;
    var l = t.ref;
    if ("ref" in r) {
      var i = {};
      for (var u in r) u !== "ref" && (i[u] = r[u]);
    } else i = r;
    return Cn(t), r = Ri(e, t, n, i, l, a), u = Oi(), e !== null && !Ue ? (Fi(e, t, a), It(e, t, a)) : (de && u && yi(t), t.flags |= 1, He(e, t, r, a), t.child);
  }
  function hc(e, t, n, r, a) {
    if (e === null) {
      var l = n.type;
      return typeof l == "function" && !hi(l) && l.defaultProps === void 0 && n.compare === null ? (t.tag = 15, t.type = l, mc(e, t, l, r, a)) : (e = Ma(n.type, null, r, t, t.mode, a), e.ref = t.ref, e.return = t, t.child = e);
    }
    if (l = e.child, !lo(e, a)) {
      var i = l.memoizedProps;
      if (n = n.compare, n = n !== null ? n : Tr, n(i, r) && e.ref === t.ref) return It(e, t, a);
    }
    return t.flags |= 1, e = Lt(l, r), e.ref = t.ref, e.return = t, t.child = e;
  }
  function mc(e, t, n, r, a) {
    if (e !== null) {
      var l = e.memoizedProps;
      if (Tr(l, r) && e.ref === t.ref) if (Ue = false, t.pendingProps = r = l, lo(e, a)) (e.flags & 131072) !== 0 && (Ue = true);
      else return t.lanes = e.lanes, It(e, t, a);
    }
    return Zi(e, t, n, r, a);
  }
  function gc(e, t, n) {
    var r = t.pendingProps, a = r.children, l = e !== null ? e.memoizedState : null;
    if (r.mode === "hidden") {
      if ((t.flags & 128) !== 0) {
        if (r = l !== null ? l.baseLanes | n : n, e !== null) {
          for (a = t.child = e.child, l = 0; a !== null; ) l = l | a.lanes | a.childLanes, a = a.sibling;
          t.childLanes = l & ~r;
        } else t.childLanes = 0, t.child = null;
        return yc(e, t, r, n);
      }
      if ((n & 536870912) !== 0) t.memoizedState = { baseLanes: 0, cachePool: null }, e !== null && Ha(t, l !== null ? l.cachePool : null), l !== null ? mu(t, l) : Li(), rc(t);
      else return t.lanes = t.childLanes = 536870912, yc(e, t, l !== null ? l.baseLanes | n : n, n);
    } else l !== null ? (Ha(t, l.cachePool), mu(t, l), tn(), t.memoizedState = null) : (e !== null && Ha(t, null), Li(), tn());
    return He(e, t, a, n), t.child;
  }
  function yc(e, t, n, r) {
    var a = Ei();
    return a = a === null ? null : { parent: De._currentValue, pool: a }, t.memoizedState = { baseLanes: n, cachePool: a }, e !== null && Ha(t, null), Li(), rc(t), e !== null && Dr(e, t, r, true), null;
  }
  function il(e, t) {
    var n = t.ref;
    if (n === null) e !== null && e.ref !== null && (t.flags |= 4194816);
    else {
      if (typeof n != "function" && typeof n != "object") throw Error(o(284));
      (e === null || e.ref !== n) && (t.flags |= 4194816);
    }
  }
  function Zi(e, t, n, r, a) {
    return Cn(t), n = Ri(e, t, n, r, void 0, a), r = Oi(), e !== null && !Ue ? (Fi(e, t, a), It(e, t, a)) : (de && r && yi(t), t.flags |= 1, He(e, t, n, a), t.child);
  }
  function vc(e, t, n, r, a, l) {
    return Cn(t), t.updateQueue = null, n = yu(t, r, n, a), gu(e), r = Oi(), e !== null && !Ue ? (Fi(e, t, l), It(e, t, l)) : (de && r && yi(t), t.flags |= 1, He(e, t, n, l), t.child);
  }
  function bc(e, t, n, r, a) {
    if (Cn(t), t.stateNode === null) {
      var l = Gn, i = n.contextType;
      typeof i == "object" && i !== null && (l = Ve(i)), l = new n(r, l), t.memoizedState = l.state !== null && l.state !== void 0 ? l.state : null, l.updater = Gi, t.stateNode = l, l._reactInternals = t, l = t.stateNode, l.props = r, l.state = t.memoizedState, l.refs = {}, ji(t), i = n.contextType, l.context = typeof i == "object" && i !== null ? Ve(i) : Gn, l.state = t.memoizedState, i = n.getDerivedStateFromProps, typeof i == "function" && (Yi(t, n, i, r), l.state = t.memoizedState), typeof n.getDerivedStateFromProps == "function" || typeof l.getSnapshotBeforeUpdate == "function" || typeof l.UNSAFE_componentWillMount != "function" && typeof l.componentWillMount != "function" || (i = l.state, typeof l.componentWillMount == "function" && l.componentWillMount(), typeof l.UNSAFE_componentWillMount == "function" && l.UNSAFE_componentWillMount(), i !== l.state && Gi.enqueueReplaceState(l, l.state, null), $r(t, r, l, a), Hr(), l.state = t.memoizedState), typeof l.componentDidMount == "function" && (t.flags |= 4194308), r = true;
    } else if (e === null) {
      l = t.stateNode;
      var u = t.memoizedProps, d = Ln(n, u);
      l.props = d;
      var y = l.context, w = n.contextType;
      i = Gn, typeof w == "object" && w !== null && (i = Ve(w));
      var E = n.getDerivedStateFromProps;
      w = typeof E == "function" || typeof l.getSnapshotBeforeUpdate == "function", u = t.pendingProps !== u, w || typeof l.UNSAFE_componentWillReceiveProps != "function" && typeof l.componentWillReceiveProps != "function" || (u || y !== i) && lc(t, l, r, i), Gt = false;
      var v = t.memoizedState;
      l.state = v, $r(t, r, l, a), Hr(), y = t.memoizedState, u || v !== y || Gt ? (typeof E == "function" && (Yi(t, n, E, r), y = t.memoizedState), (d = Gt || ac(t, n, d, r, v, y, i)) ? (w || typeof l.UNSAFE_componentWillMount != "function" && typeof l.componentWillMount != "function" || (typeof l.componentWillMount == "function" && l.componentWillMount(), typeof l.UNSAFE_componentWillMount == "function" && l.UNSAFE_componentWillMount()), typeof l.componentDidMount == "function" && (t.flags |= 4194308)) : (typeof l.componentDidMount == "function" && (t.flags |= 4194308), t.memoizedProps = r, t.memoizedState = y), l.props = r, l.state = y, l.context = i, r = d) : (typeof l.componentDidMount == "function" && (t.flags |= 4194308), r = false);
    } else {
      l = t.stateNode, Ci(e, t), i = t.memoizedProps, w = Ln(n, i), l.props = w, E = t.pendingProps, v = l.context, y = n.contextType, d = Gn, typeof y == "object" && y !== null && (d = Ve(y)), u = n.getDerivedStateFromProps, (y = typeof u == "function" || typeof l.getSnapshotBeforeUpdate == "function") || typeof l.UNSAFE_componentWillReceiveProps != "function" && typeof l.componentWillReceiveProps != "function" || (i !== E || v !== d) && lc(t, l, r, d), Gt = false, v = t.memoizedState, l.state = v, $r(t, r, l, a), Hr();
      var b = t.memoizedState;
      i !== E || v !== b || Gt || e !== null && e.dependencies !== null && Ba(e.dependencies) ? (typeof u == "function" && (Yi(t, n, u, r), b = t.memoizedState), (w = Gt || ac(t, n, w, r, v, b, d) || e !== null && e.dependencies !== null && Ba(e.dependencies)) ? (y || typeof l.UNSAFE_componentWillUpdate != "function" && typeof l.componentWillUpdate != "function" || (typeof l.componentWillUpdate == "function" && l.componentWillUpdate(r, b, d), typeof l.UNSAFE_componentWillUpdate == "function" && l.UNSAFE_componentWillUpdate(r, b, d)), typeof l.componentDidUpdate == "function" && (t.flags |= 4), typeof l.getSnapshotBeforeUpdate == "function" && (t.flags |= 1024)) : (typeof l.componentDidUpdate != "function" || i === e.memoizedProps && v === e.memoizedState || (t.flags |= 4), typeof l.getSnapshotBeforeUpdate != "function" || i === e.memoizedProps && v === e.memoizedState || (t.flags |= 1024), t.memoizedProps = r, t.memoizedState = b), l.props = r, l.state = b, l.context = d, r = w) : (typeof l.componentDidUpdate != "function" || i === e.memoizedProps && v === e.memoizedState || (t.flags |= 4), typeof l.getSnapshotBeforeUpdate != "function" || i === e.memoizedProps && v === e.memoizedState || (t.flags |= 1024), r = false);
    }
    return l = r, il(e, t), r = (t.flags & 128) !== 0, l || r ? (l = t.stateNode, n = r && typeof n.getDerivedStateFromError != "function" ? null : l.render(), t.flags |= 1, e !== null && r ? (t.child = ir(t, e.child, null, a), t.child = ir(t, null, n, a)) : He(e, t, n, a), t.memoizedState = l.state, e = t.child) : e = It(e, t, a), e;
  }
  function xc(e, t, n, r) {
    return Or(), t.flags |= 256, He(e, t, n, r), t.child;
  }
  var Ji = { dehydrated: null, treeContext: null, retryLane: 0, hydrationErrors: null };
  function eo(e) {
    return { baseLanes: e, cachePool: ou() };
  }
  function to(e, t, n) {
    return e = e !== null ? e.childLanes & ~n : 0, t && (e |= yt), e;
  }
  function wc(e, t, n) {
    var r = t.pendingProps, a = false, l = (t.flags & 128) !== 0, i;
    if ((i = l) || (i = e !== null && e.memoizedState === null ? false : (Me.current & 2) !== 0), i && (a = true, t.flags &= -129), i = (t.flags & 32) !== 0, t.flags &= -33, e === null) {
      if (de) {
        if (a ? en(t) : tn(), de) {
          var u = ze, d;
          if (d = u) {
            e: {
              for (d = u, u = Nt; d.nodeType !== 8; ) {
                if (!u) {
                  u = null;
                  break e;
                }
                if (d = kt(d.nextSibling), d === null) {
                  u = null;
                  break e;
                }
              }
              u = d;
            }
            u !== null ? (t.memoizedState = { dehydrated: u, treeContext: Sn !== null ? { id: Tt, overflow: At } : null, retryLane: 536870912, hydrationErrors: null }, d = lt(18, null, null, 0), d.stateNode = u, d.return = t, t.child = d, Ye = t, ze = null, d = true) : d = false;
          }
          d || _n(t);
        }
        if (u = t.memoizedState, u !== null && (u = u.dehydrated, u !== null)) return Io(u) ? t.lanes = 32 : t.lanes = 536870912, null;
        Mt(t);
      }
      return u = r.children, r = r.fallback, a ? (tn(), a = t.mode, u = ol({ mode: "hidden", children: u }, a), r = kn(r, a, n, null), u.return = t, r.return = t, u.sibling = r, t.child = u, a = t.child, a.memoizedState = eo(n), a.childLanes = to(e, i, n), t.memoizedState = Ji, r) : (en(t), no(t, u));
    }
    if (d = e.memoizedState, d !== null && (u = d.dehydrated, u !== null)) {
      if (l) t.flags & 256 ? (en(t), t.flags &= -257, t = ro(e, t, n)) : t.memoizedState !== null ? (tn(), t.child = e.child, t.flags |= 128, t = null) : (tn(), a = r.fallback, u = t.mode, r = ol({ mode: "visible", children: r.children }, u), a = kn(a, u, n, null), a.flags |= 2, r.return = t, a.return = t, r.sibling = a, t.child = r, ir(t, e.child, null, n), r = t.child, r.memoizedState = eo(n), r.childLanes = to(e, i, n), t.memoizedState = Ji, t = a);
      else if (en(t), Io(u)) {
        if (i = u.nextSibling && u.nextSibling.dataset, i) var y = i.dgst;
        i = y, r = Error(o(419)), r.stack = "", r.digest = i, Fr({ value: r, source: null, stack: null }), t = ro(e, t, n);
      } else if (Ue || Dr(e, t, n, false), i = (n & e.childLanes) !== 0, Ue || i) {
        if (i = Se, i !== null && (r = n & -n, r = (r & 42) !== 0 ? 1 : Il(r), r = (r & (i.suspendedLanes | n)) !== 0 ? 0 : r, r !== 0 && r !== d.retryLane)) throw d.retryLane = r, Yn(e, r), ct(i, e, r), fc;
        u.data === "$?" || ko(), t = ro(e, t, n);
      } else u.data === "$?" ? (t.flags |= 192, t.child = e.child, t = null) : (e = d.treeContext, ze = kt(u.nextSibling), Ye = t, de = true, En = null, Nt = false, e !== null && (ht[mt++] = Tt, ht[mt++] = At, ht[mt++] = Sn, Tt = e.id, At = e.overflow, Sn = t), t = no(t, r.children), t.flags |= 4096);
      return t;
    }
    return a ? (tn(), a = r.fallback, u = t.mode, d = e.child, y = d.sibling, r = Lt(d, { mode: "hidden", children: r.children }), r.subtreeFlags = d.subtreeFlags & 65011712, y !== null ? a = Lt(y, a) : (a = kn(a, u, n, null), a.flags |= 2), a.return = t, r.return = t, r.sibling = a, t.child = r, r = a, a = t.child, u = e.child.memoizedState, u === null ? u = eo(n) : (d = u.cachePool, d !== null ? (y = De._currentValue, d = d.parent !== y ? { parent: y, pool: y } : d) : d = ou(), u = { baseLanes: u.baseLanes | n, cachePool: d }), a.memoizedState = u, a.childLanes = to(e, i, n), t.memoizedState = Ji, r) : (en(t), n = e.child, e = n.sibling, n = Lt(n, { mode: "visible", children: r.children }), n.return = t, n.sibling = null, e !== null && (i = t.deletions, i === null ? (t.deletions = [e], t.flags |= 16) : i.push(e)), t.child = n, t.memoizedState = null, n);
  }
  function no(e, t) {
    return t = ol({ mode: "visible", children: t }, e.mode), t.return = e, e.child = t;
  }
  function ol(e, t) {
    return e = lt(22, e, null, t), e.lanes = 0, e.stateNode = { _visibility: 1, _pendingMarkers: null, _retryCache: null, _transitions: null }, e;
  }
  function ro(e, t, n) {
    return ir(t, e.child, null, n), e = no(t, t.pendingProps.children), e.flags |= 2, t.memoizedState = null, e;
  }
  function kc(e, t, n) {
    e.lanes |= t;
    var r = e.alternate;
    r !== null && (r.lanes |= t), wi(e.return, t, n);
  }
  function ao(e, t, n, r, a) {
    var l = e.memoizedState;
    l === null ? e.memoizedState = { isBackwards: t, rendering: null, renderingStartTime: 0, last: r, tail: n, tailMode: a } : (l.isBackwards = t, l.rendering = null, l.renderingStartTime = 0, l.last = r, l.tail = n, l.tailMode = a);
  }
  function Sc(e, t, n) {
    var r = t.pendingProps, a = r.revealOrder, l = r.tail;
    if (He(e, t, r.children, n), r = Me.current, (r & 2) !== 0) r = r & 1 | 2, t.flags |= 128;
    else {
      if (e !== null && (e.flags & 128) !== 0) e: for (e = t.child; e !== null; ) {
        if (e.tag === 13) e.memoizedState !== null && kc(e, n, t);
        else if (e.tag === 19) kc(e, n, t);
        else if (e.child !== null) {
          e.child.return = e, e = e.child;
          continue;
        }
        if (e === t) break e;
        for (; e.sibling === null; ) {
          if (e.return === null || e.return === t) break e;
          e = e.return;
        }
        e.sibling.return = e.return, e = e.sibling;
      }
      r &= 1;
    }
    switch (A(Me, r), a) {
      case "forwards":
        for (n = t.child, a = null; n !== null; ) e = n.alternate, e !== null && rl(e) === null && (a = n), n = n.sibling;
        n = a, n === null ? (a = t.child, t.child = null) : (a = n.sibling, n.sibling = null), ao(t, false, a, n, l);
        break;
      case "backwards":
        for (n = null, a = t.child, t.child = null; a !== null; ) {
          if (e = a.alternate, e !== null && rl(e) === null) {
            t.child = a;
            break;
          }
          e = a.sibling, a.sibling = n, n = a, a = e;
        }
        ao(t, true, n, null, l);
        break;
      case "together":
        ao(t, false, null, null, void 0);
        break;
      default:
        t.memoizedState = null;
    }
    return t.child;
  }
  function It(e, t, n) {
    if (e !== null && (t.dependencies = e.dependencies), on |= t.lanes, (n & t.childLanes) === 0) if (e !== null) {
      if (Dr(e, t, n, false), (n & t.childLanes) === 0) return null;
    } else return null;
    if (e !== null && t.child !== e.child) throw Error(o(153));
    if (t.child !== null) {
      for (e = t.child, n = Lt(e, e.pendingProps), t.child = n, n.return = t; e.sibling !== null; ) e = e.sibling, n = n.sibling = Lt(e, e.pendingProps), n.return = t;
      n.sibling = null;
    }
    return t.child;
  }
  function lo(e, t) {
    return (e.lanes & t) !== 0 ? true : (e = e.dependencies, !!(e !== null && Ba(e)));
  }
  function zp(e, t, n) {
    switch (t.tag) {
      case 3:
        _e(t, t.stateNode.containerInfo), Yt(t, De, e.memoizedState.cache), Or();
        break;
      case 27:
      case 5:
        Rl(t);
        break;
      case 4:
        _e(t, t.stateNode.containerInfo);
        break;
      case 10:
        Yt(t, t.type, t.memoizedProps.value);
        break;
      case 13:
        var r = t.memoizedState;
        if (r !== null) return r.dehydrated !== null ? (en(t), t.flags |= 128, null) : (n & t.child.childLanes) !== 0 ? wc(e, t, n) : (en(t), e = It(e, t, n), e !== null ? e.sibling : null);
        en(t);
        break;
      case 19:
        var a = (e.flags & 128) !== 0;
        if (r = (n & t.childLanes) !== 0, r || (Dr(e, t, n, false), r = (n & t.childLanes) !== 0), a) {
          if (r) return Sc(e, t, n);
          t.flags |= 128;
        }
        if (a = t.memoizedState, a !== null && (a.rendering = null, a.tail = null, a.lastEffect = null), A(Me, Me.current), r) break;
        return null;
      case 22:
      case 23:
        return t.lanes = 0, gc(e, t, n);
      case 24:
        Yt(t, De, e.memoizedState.cache);
    }
    return It(e, t, n);
  }
  function Nc(e, t, n) {
    if (e !== null) if (e.memoizedProps !== t.pendingProps) Ue = true;
    else {
      if (!lo(e, n) && (t.flags & 128) === 0) return Ue = false, zp(e, t, n);
      Ue = (e.flags & 131072) !== 0;
    }
    else Ue = false, de && (t.flags & 1048576) !== 0 && eu(t, Ua, t.index);
    switch (t.lanes = 0, t.tag) {
      case 16:
        e: {
          e = t.pendingProps;
          var r = t.elementType, a = r._init;
          if (r = a(r._payload), t.type = r, typeof r == "function") hi(r) ? (e = Ln(r, e), t.tag = 1, t = bc(null, t, r, e, n)) : (t.tag = 0, t = Zi(null, t, r, e, n));
          else {
            if (r != null) {
              if (a = r.$$typeof, a === Ae) {
                t.tag = 11, t = pc(null, t, r, e, n);
                break e;
              } else if (a === T) {
                t.tag = 14, t = hc(null, t, r, e, n);
                break e;
              }
            }
            throw t = U(r) || r, Error(o(306, t, ""));
          }
        }
        return t;
      case 0:
        return Zi(e, t, t.type, t.pendingProps, n);
      case 1:
        return r = t.type, a = Ln(r, t.pendingProps), bc(e, t, r, a, n);
      case 3:
        e: {
          if (_e(t, t.stateNode.containerInfo), e === null) throw Error(o(387));
          r = t.pendingProps;
          var l = t.memoizedState;
          a = l.element, Ci(e, t), $r(t, r, null, n);
          var i = t.memoizedState;
          if (r = i.cache, Yt(t, De, r), r !== l.cache && ki(t, [De], n, true), Hr(), r = i.element, l.isDehydrated) if (l = { element: r, isDehydrated: false, cache: i.cache }, t.updateQueue.baseState = l, t.memoizedState = l, t.flags & 256) {
            t = xc(e, t, r, n);
            break e;
          } else if (r !== a) {
            a = ft(Error(o(424)), t), Fr(a), t = xc(e, t, r, n);
            break e;
          } else {
            switch (e = t.stateNode.containerInfo, e.nodeType) {
              case 9:
                e = e.body;
                break;
              default:
                e = e.nodeName === "HTML" ? e.ownerDocument.body : e;
            }
            for (ze = kt(e.firstChild), Ye = t, de = true, En = null, Nt = true, n = nc(t, null, r, n), t.child = n; n; ) n.flags = n.flags & -3 | 4096, n = n.sibling;
          }
          else {
            if (Or(), r === a) {
              t = It(e, t, n);
              break e;
            }
            He(e, t, r, n);
          }
          t = t.child;
        }
        return t;
      case 26:
        return il(e, t), e === null ? (n = Cd(t.type, null, t.pendingProps, null)) ? t.memoizedState = n : de || (n = t.type, e = t.pendingProps, r = wl(Y.current).createElement(n), r[Ke] = t, r[Ge] = e, qe(r, n, e), Ie(r), t.stateNode = r) : t.memoizedState = Cd(t.type, e.memoizedProps, t.pendingProps, e.memoizedState), null;
      case 27:
        return Rl(t), e === null && de && (r = t.stateNode = Ed(t.type, t.pendingProps, Y.current), Ye = t, Nt = true, a = ze, dn(t.type) ? (Uo = a, ze = kt(r.firstChild)) : ze = a), He(e, t, t.pendingProps.children, n), il(e, t), e === null && (t.flags |= 4194304), t.child;
      case 5:
        return e === null && de && ((a = r = ze) && (r = rh(r, t.type, t.pendingProps, Nt), r !== null ? (t.stateNode = r, Ye = t, ze = kt(r.firstChild), Nt = false, a = true) : a = false), a || _n(t)), Rl(t), a = t.type, l = t.pendingProps, i = e !== null ? e.memoizedProps : null, r = l.children, Fo(a, l) ? r = null : i !== null && Fo(a, i) && (t.flags |= 32), t.memoizedState !== null && (a = Ri(e, t, wp, null, null, n), fa._currentValue = a), il(e, t), He(e, t, r, n), t.child;
      case 6:
        return e === null && de && ((e = n = ze) && (n = ah(n, t.pendingProps, Nt), n !== null ? (t.stateNode = n, Ye = t, ze = null, e = true) : e = false), e || _n(t)), null;
      case 13:
        return wc(e, t, n);
      case 4:
        return _e(t, t.stateNode.containerInfo), r = t.pendingProps, e === null ? t.child = ir(t, null, r, n) : He(e, t, r, n), t.child;
      case 11:
        return pc(e, t, t.type, t.pendingProps, n);
      case 7:
        return He(e, t, t.pendingProps, n), t.child;
      case 8:
        return He(e, t, t.pendingProps.children, n), t.child;
      case 12:
        return He(e, t, t.pendingProps.children, n), t.child;
      case 10:
        return r = t.pendingProps, Yt(t, t.type, r.value), He(e, t, r.children, n), t.child;
      case 9:
        return a = t.type._context, r = t.pendingProps.children, Cn(t), a = Ve(a), r = r(a), t.flags |= 1, He(e, t, r, n), t.child;
      case 14:
        return hc(e, t, t.type, t.pendingProps, n);
      case 15:
        return mc(e, t, t.type, t.pendingProps, n);
      case 19:
        return Sc(e, t, n);
      case 31:
        return r = t.pendingProps, n = t.mode, r = { mode: r.mode, children: r.children }, e === null ? (n = ol(r, n), n.ref = t.ref, t.child = n, n.return = t, t = n) : (n = Lt(e.child, r), n.ref = t.ref, t.child = n, n.return = t, t = n), t;
      case 22:
        return gc(e, t, n);
      case 24:
        return Cn(t), r = Ve(De), e === null ? (a = Ei(), a === null && (a = Se, l = Si(), a.pooledCache = l, l.refCount++, l !== null && (a.pooledCacheLanes |= n), a = l), t.memoizedState = { parent: r, cache: a }, ji(t), Yt(t, De, a)) : ((e.lanes & n) !== 0 && (Ci(e, t), $r(t, null, null, n), Hr()), a = e.memoizedState, l = t.memoizedState, a.parent !== r ? (a = { parent: r, cache: r }, t.memoizedState = a, t.lanes === 0 && (t.memoizedState = t.updateQueue.baseState = a), Yt(t, De, r)) : (r = l.cache, Yt(t, De, r), r !== a.cache && ki(t, [De], n, true))), He(e, t, t.pendingProps.children, n), t.child;
      case 29:
        throw t.pendingProps;
    }
    throw Error(o(156, t.tag));
  }
  function Ut(e) {
    e.flags |= 4;
  }
  function Ec(e, t) {
    if (t.type !== "stylesheet" || (t.state.loading & 4) !== 0) e.flags &= -16777217;
    else if (e.flags |= 16777216, !Ad(t)) {
      if (t = gt.current, t !== null && ((le & 4194048) === le ? Dt !== null : (le & 62914560) !== le && (le & 536870912) === 0 || t !== Dt)) throw Br = _i, su;
      e.flags |= 8192;
    }
  }
  function sl(e, t) {
    t !== null && (e.flags |= 4), e.flags & 16384 && (t = e.tag !== 22 ? rs() : 536870912, e.lanes |= t, cr |= t);
  }
  function Xr(e, t) {
    if (!de) switch (e.tailMode) {
      case "hidden":
        t = e.tail;
        for (var n = null; t !== null; ) t.alternate !== null && (n = t), t = t.sibling;
        n === null ? e.tail = null : n.sibling = null;
        break;
      case "collapsed":
        n = e.tail;
        for (var r = null; n !== null; ) n.alternate !== null && (r = n), n = n.sibling;
        r === null ? t || e.tail === null ? e.tail = null : e.tail.sibling = null : r.sibling = null;
    }
  }
  function Ce(e) {
    var t = e.alternate !== null && e.alternate.child === e.child, n = 0, r = 0;
    if (t) for (var a = e.child; a !== null; ) n |= a.lanes | a.childLanes, r |= a.subtreeFlags & 65011712, r |= a.flags & 65011712, a.return = e, a = a.sibling;
    else for (a = e.child; a !== null; ) n |= a.lanes | a.childLanes, r |= a.subtreeFlags, r |= a.flags, a.return = e, a = a.sibling;
    return e.subtreeFlags |= r, e.childLanes = n, t;
  }
  function Pp(e, t, n) {
    var r = t.pendingProps;
    switch (vi(t), t.tag) {
      case 31:
      case 16:
      case 15:
      case 0:
      case 11:
      case 7:
      case 8:
      case 12:
      case 9:
      case 14:
        return Ce(t), null;
      case 1:
        return Ce(t), null;
      case 3:
        return n = t.stateNode, r = null, e !== null && (r = e.memoizedState.cache), t.memoizedState.cache !== r && (t.flags |= 2048), Ot(De), qt(), n.pendingContext && (n.context = n.pendingContext, n.pendingContext = null), (e === null || e.child === null) && (Rr(t) ? Ut(t) : e === null || e.memoizedState.isDehydrated && (t.flags & 256) === 0 || (t.flags |= 1024, ru())), Ce(t), null;
      case 26:
        return n = t.memoizedState, e === null ? (Ut(t), n !== null ? (Ce(t), Ec(t, n)) : (Ce(t), t.flags &= -16777217)) : n ? n !== e.memoizedState ? (Ut(t), Ce(t), Ec(t, n)) : (Ce(t), t.flags &= -16777217) : (e.memoizedProps !== r && Ut(t), Ce(t), t.flags &= -16777217), null;
      case 27:
        ba(t), n = Y.current;
        var a = t.type;
        if (e !== null && t.stateNode != null) e.memoizedProps !== r && Ut(t);
        else {
          if (!r) {
            if (t.stateNode === null) throw Error(o(166));
            return Ce(t), null;
          }
          e = B.current, Rr(t) ? tu(t) : (e = Ed(a, r, n), t.stateNode = e, Ut(t));
        }
        return Ce(t), null;
      case 5:
        if (ba(t), n = t.type, e !== null && t.stateNode != null) e.memoizedProps !== r && Ut(t);
        else {
          if (!r) {
            if (t.stateNode === null) throw Error(o(166));
            return Ce(t), null;
          }
          if (e = B.current, Rr(t)) tu(t);
          else {
            switch (a = wl(Y.current), e) {
              case 1:
                e = a.createElementNS("http://www.w3.org/2000/svg", n);
                break;
              case 2:
                e = a.createElementNS("http://www.w3.org/1998/Math/MathML", n);
                break;
              default:
                switch (n) {
                  case "svg":
                    e = a.createElementNS("http://www.w3.org/2000/svg", n);
                    break;
                  case "math":
                    e = a.createElementNS("http://www.w3.org/1998/Math/MathML", n);
                    break;
                  case "script":
                    e = a.createElement("div"), e.innerHTML = "<script><\/script>", e = e.removeChild(e.firstChild);
                    break;
                  case "select":
                    e = typeof r.is == "string" ? a.createElement("select", { is: r.is }) : a.createElement("select"), r.multiple ? e.multiple = true : r.size && (e.size = r.size);
                    break;
                  default:
                    e = typeof r.is == "string" ? a.createElement(n, { is: r.is }) : a.createElement(n);
                }
            }
            e[Ke] = t, e[Ge] = r;
            e: for (a = t.child; a !== null; ) {
              if (a.tag === 5 || a.tag === 6) e.appendChild(a.stateNode);
              else if (a.tag !== 4 && a.tag !== 27 && a.child !== null) {
                a.child.return = a, a = a.child;
                continue;
              }
              if (a === t) break e;
              for (; a.sibling === null; ) {
                if (a.return === null || a.return === t) break e;
                a = a.return;
              }
              a.sibling.return = a.return, a = a.sibling;
            }
            t.stateNode = e;
            e: switch (qe(e, n, r), n) {
              case "button":
              case "input":
              case "select":
              case "textarea":
                e = !!r.autoFocus;
                break e;
              case "img":
                e = true;
                break e;
              default:
                e = false;
            }
            e && Ut(t);
          }
        }
        return Ce(t), t.flags &= -16777217, null;
      case 6:
        if (e && t.stateNode != null) e.memoizedProps !== r && Ut(t);
        else {
          if (typeof r != "string" && t.stateNode === null) throw Error(o(166));
          if (e = Y.current, Rr(t)) {
            if (e = t.stateNode, n = t.memoizedProps, r = null, a = Ye, a !== null) switch (a.tag) {
              case 27:
              case 5:
                r = a.memoizedProps;
            }
            e[Ke] = t, e = !!(e.nodeValue === n || r !== null && r.suppressHydrationWarning === true || vd(e.nodeValue, n)), e || _n(t);
          } else e = wl(e).createTextNode(r), e[Ke] = t, t.stateNode = e;
        }
        return Ce(t), null;
      case 13:
        if (r = t.memoizedState, e === null || e.memoizedState !== null && e.memoizedState.dehydrated !== null) {
          if (a = Rr(t), r !== null && r.dehydrated !== null) {
            if (e === null) {
              if (!a) throw Error(o(318));
              if (a = t.memoizedState, a = a !== null ? a.dehydrated : null, !a) throw Error(o(317));
              a[Ke] = t;
            } else Or(), (t.flags & 128) === 0 && (t.memoizedState = null), t.flags |= 4;
            Ce(t), a = false;
          } else a = ru(), e !== null && e.memoizedState !== null && (e.memoizedState.hydrationErrors = a), a = true;
          if (!a) return t.flags & 256 ? (Mt(t), t) : (Mt(t), null);
        }
        if (Mt(t), (t.flags & 128) !== 0) return t.lanes = n, t;
        if (n = r !== null, e = e !== null && e.memoizedState !== null, n) {
          r = t.child, a = null, r.alternate !== null && r.alternate.memoizedState !== null && r.alternate.memoizedState.cachePool !== null && (a = r.alternate.memoizedState.cachePool.pool);
          var l = null;
          r.memoizedState !== null && r.memoizedState.cachePool !== null && (l = r.memoizedState.cachePool.pool), l !== a && (r.flags |= 2048);
        }
        return n !== e && n && (t.child.flags |= 8192), sl(t, t.updateQueue), Ce(t), null;
      case 4:
        return qt(), e === null && Lo(t.stateNode.containerInfo), Ce(t), null;
      case 10:
        return Ot(t.type), Ce(t), null;
      case 19:
        if (F(Me), a = t.memoizedState, a === null) return Ce(t), null;
        if (r = (t.flags & 128) !== 0, l = a.rendering, l === null) if (r) Xr(a, false);
        else {
          if (Pe !== 0 || e !== null && (e.flags & 128) !== 0) for (e = t.child; e !== null; ) {
            if (l = rl(e), l !== null) {
              for (t.flags |= 128, Xr(a, false), e = l.updateQueue, t.updateQueue = e, sl(t, e), t.subtreeFlags = 0, e = n, n = t.child; n !== null; ) Js(n, e), n = n.sibling;
              return A(Me, Me.current & 1 | 2), t.child;
            }
            e = e.sibling;
          }
          a.tail !== null && St() > dl && (t.flags |= 128, r = true, Xr(a, false), t.lanes = 4194304);
        }
        else {
          if (!r) if (e = rl(l), e !== null) {
            if (t.flags |= 128, r = true, e = e.updateQueue, t.updateQueue = e, sl(t, e), Xr(a, true), a.tail === null && a.tailMode === "hidden" && !l.alternate && !de) return Ce(t), null;
          } else 2 * St() - a.renderingStartTime > dl && n !== 536870912 && (t.flags |= 128, r = true, Xr(a, false), t.lanes = 4194304);
          a.isBackwards ? (l.sibling = t.child, t.child = l) : (e = a.last, e !== null ? e.sibling = l : t.child = l, a.last = l);
        }
        return a.tail !== null ? (t = a.tail, a.rendering = t, a.tail = t.sibling, a.renderingStartTime = St(), t.sibling = null, e = Me.current, A(Me, r ? e & 1 | 2 : e & 1), t) : (Ce(t), null);
      case 22:
      case 23:
        return Mt(t), Ti(), r = t.memoizedState !== null, e !== null ? e.memoizedState !== null !== r && (t.flags |= 8192) : r && (t.flags |= 8192), r ? (n & 536870912) !== 0 && (t.flags & 128) === 0 && (Ce(t), t.subtreeFlags & 6 && (t.flags |= 8192)) : Ce(t), n = t.updateQueue, n !== null && sl(t, n.retryQueue), n = null, e !== null && e.memoizedState !== null && e.memoizedState.cachePool !== null && (n = e.memoizedState.cachePool.pool), r = null, t.memoizedState !== null && t.memoizedState.cachePool !== null && (r = t.memoizedState.cachePool.pool), r !== n && (t.flags |= 2048), e !== null && F(zn), null;
      case 24:
        return n = null, e !== null && (n = e.memoizedState.cache), t.memoizedState.cache !== n && (t.flags |= 2048), Ot(De), Ce(t), null;
      case 25:
        return null;
      case 30:
        return null;
    }
    throw Error(o(156, t.tag));
  }
  function Lp(e, t) {
    switch (vi(t), t.tag) {
      case 1:
        return e = t.flags, e & 65536 ? (t.flags = e & -65537 | 128, t) : null;
      case 3:
        return Ot(De), qt(), e = t.flags, (e & 65536) !== 0 && (e & 128) === 0 ? (t.flags = e & -65537 | 128, t) : null;
      case 26:
      case 27:
      case 5:
        return ba(t), null;
      case 13:
        if (Mt(t), e = t.memoizedState, e !== null && e.dehydrated !== null) {
          if (t.alternate === null) throw Error(o(340));
          Or();
        }
        return e = t.flags, e & 65536 ? (t.flags = e & -65537 | 128, t) : null;
      case 19:
        return F(Me), null;
      case 4:
        return qt(), null;
      case 10:
        return Ot(t.type), null;
      case 22:
      case 23:
        return Mt(t), Ti(), e !== null && F(zn), e = t.flags, e & 65536 ? (t.flags = e & -65537 | 128, t) : null;
      case 24:
        return Ot(De), null;
      case 25:
        return null;
      default:
        return null;
    }
  }
  function _c(e, t) {
    switch (vi(t), t.tag) {
      case 3:
        Ot(De), qt();
        break;
      case 26:
      case 27:
      case 5:
        ba(t);
        break;
      case 4:
        qt();
        break;
      case 13:
        Mt(t);
        break;
      case 19:
        F(Me);
        break;
      case 10:
        Ot(t.type);
        break;
      case 22:
      case 23:
        Mt(t), Ti(), e !== null && F(zn);
        break;
      case 24:
        Ot(De);
    }
  }
  function Zr(e, t) {
    try {
      var n = t.updateQueue, r = n !== null ? n.lastEffect : null;
      if (r !== null) {
        var a = r.next;
        n = a;
        do {
          if ((n.tag & e) === e) {
            r = void 0;
            var l = n.create, i = n.inst;
            r = l(), i.destroy = r;
          }
          n = n.next;
        } while (n !== a);
      }
    } catch (u) {
      xe(t, t.return, u);
    }
  }
  function nn(e, t, n) {
    try {
      var r = t.updateQueue, a = r !== null ? r.lastEffect : null;
      if (a !== null) {
        var l = a.next;
        r = l;
        do {
          if ((r.tag & e) === e) {
            var i = r.inst, u = i.destroy;
            if (u !== void 0) {
              i.destroy = void 0, a = t;
              var d = n, y = u;
              try {
                y();
              } catch (w) {
                xe(a, d, w);
              }
            }
          }
          r = r.next;
        } while (r !== l);
      }
    } catch (w) {
      xe(t, t.return, w);
    }
  }
  function jc(e) {
    var t = e.updateQueue;
    if (t !== null) {
      var n = e.stateNode;
      try {
        hu(t, n);
      } catch (r) {
        xe(e, e.return, r);
      }
    }
  }
  function Cc(e, t, n) {
    n.props = Ln(e.type, e.memoizedProps), n.state = e.memoizedState;
    try {
      n.componentWillUnmount();
    } catch (r) {
      xe(e, t, r);
    }
  }
  function Jr(e, t) {
    try {
      var n = e.ref;
      if (n !== null) {
        switch (e.tag) {
          case 26:
          case 27:
          case 5:
            var r = e.stateNode;
            break;
          case 30:
            r = e.stateNode;
            break;
          default:
            r = e.stateNode;
        }
        typeof n == "function" ? e.refCleanup = n(r) : n.current = r;
      }
    } catch (a) {
      xe(e, t, a);
    }
  }
  function Et(e, t) {
    var n = e.ref, r = e.refCleanup;
    if (n !== null) if (typeof r == "function") try {
      r();
    } catch (a) {
      xe(e, t, a);
    } finally {
      e.refCleanup = null, e = e.alternate, e != null && (e.refCleanup = null);
    }
    else if (typeof n == "function") try {
      n(null);
    } catch (a) {
      xe(e, t, a);
    }
    else n.current = null;
  }
  function zc(e) {
    var t = e.type, n = e.memoizedProps, r = e.stateNode;
    try {
      e: switch (t) {
        case "button":
        case "input":
        case "select":
        case "textarea":
          n.autoFocus && r.focus();
          break e;
        case "img":
          n.src ? r.src = n.src : n.srcSet && (r.srcset = n.srcSet);
      }
    } catch (a) {
      xe(e, e.return, a);
    }
  }
  function io(e, t, n) {
    try {
      var r = e.stateNode;
      Zp(r, e.type, n, t), r[Ge] = t;
    } catch (a) {
      xe(e, e.return, a);
    }
  }
  function Pc(e) {
    return e.tag === 5 || e.tag === 3 || e.tag === 26 || e.tag === 27 && dn(e.type) || e.tag === 4;
  }
  function oo(e) {
    e: for (; ; ) {
      for (; e.sibling === null; ) {
        if (e.return === null || Pc(e.return)) return null;
        e = e.return;
      }
      for (e.sibling.return = e.return, e = e.sibling; e.tag !== 5 && e.tag !== 6 && e.tag !== 18; ) {
        if (e.tag === 27 && dn(e.type) || e.flags & 2 || e.child === null || e.tag === 4) continue e;
        e.child.return = e, e = e.child;
      }
      if (!(e.flags & 2)) return e.stateNode;
    }
  }
  function so(e, t, n) {
    var r = e.tag;
    if (r === 5 || r === 6) e = e.stateNode, t ? (n.nodeType === 9 ? n.body : n.nodeName === "HTML" ? n.ownerDocument.body : n).insertBefore(e, t) : (t = n.nodeType === 9 ? n.body : n.nodeName === "HTML" ? n.ownerDocument.body : n, t.appendChild(e), n = n._reactRootContainer, n != null || t.onclick !== null || (t.onclick = xl));
    else if (r !== 4 && (r === 27 && dn(e.type) && (n = e.stateNode, t = null), e = e.child, e !== null)) for (so(e, t, n), e = e.sibling; e !== null; ) so(e, t, n), e = e.sibling;
  }
  function ul(e, t, n) {
    var r = e.tag;
    if (r === 5 || r === 6) e = e.stateNode, t ? n.insertBefore(e, t) : n.appendChild(e);
    else if (r !== 4 && (r === 27 && dn(e.type) && (n = e.stateNode), e = e.child, e !== null)) for (ul(e, t, n), e = e.sibling; e !== null; ) ul(e, t, n), e = e.sibling;
  }
  function Lc(e) {
    var t = e.stateNode, n = e.memoizedProps;
    try {
      for (var r = e.type, a = t.attributes; a.length; ) t.removeAttributeNode(a[0]);
      qe(t, r, n), t[Ke] = e, t[Ge] = n;
    } catch (l) {
      xe(e, e.return, l);
    }
  }
  var Bt = false, Te = false, uo = false, Tc = typeof WeakSet == "function" ? WeakSet : Set, Be = null;
  function Tp(e, t) {
    if (e = e.containerInfo, Ro = jl, e = Hs(e), oi(e)) {
      if ("selectionStart" in e) var n = { start: e.selectionStart, end: e.selectionEnd };
      else e: {
        n = (n = e.ownerDocument) && n.defaultView || window;
        var r = n.getSelection && n.getSelection();
        if (r && r.rangeCount !== 0) {
          n = r.anchorNode;
          var a = r.anchorOffset, l = r.focusNode;
          r = r.focusOffset;
          try {
            n.nodeType, l.nodeType;
          } catch {
            n = null;
            break e;
          }
          var i = 0, u = -1, d = -1, y = 0, w = 0, E = e, v = null;
          t: for (; ; ) {
            for (var b; E !== n || a !== 0 && E.nodeType !== 3 || (u = i + a), E !== l || r !== 0 && E.nodeType !== 3 || (d = i + r), E.nodeType === 3 && (i += E.nodeValue.length), (b = E.firstChild) !== null; ) v = E, E = b;
            for (; ; ) {
              if (E === e) break t;
              if (v === n && ++y === a && (u = i), v === l && ++w === r && (d = i), (b = E.nextSibling) !== null) break;
              E = v, v = E.parentNode;
            }
            E = b;
          }
          n = u === -1 || d === -1 ? null : { start: u, end: d };
        } else n = null;
      }
      n = n || { start: 0, end: 0 };
    } else n = null;
    for (Oo = { focusedElem: e, selectionRange: n }, jl = false, Be = t; Be !== null; ) if (t = Be, e = t.child, (t.subtreeFlags & 1024) !== 0 && e !== null) e.return = t, Be = e;
    else for (; Be !== null; ) {
      switch (t = Be, l = t.alternate, e = t.flags, t.tag) {
        case 0:
          break;
        case 11:
        case 15:
          break;
        case 1:
          if ((e & 1024) !== 0 && l !== null) {
            e = void 0, n = t, a = l.memoizedProps, l = l.memoizedState, r = n.stateNode;
            try {
              var Q = Ln(n.type, a, n.elementType === n.type);
              e = r.getSnapshotBeforeUpdate(Q, l), r.__reactInternalSnapshotBeforeUpdate = e;
            } catch (H) {
              xe(n, n.return, H);
            }
          }
          break;
        case 3:
          if ((e & 1024) !== 0) {
            if (e = t.stateNode.containerInfo, n = e.nodeType, n === 9) Mo(e);
            else if (n === 1) switch (e.nodeName) {
              case "HEAD":
              case "HTML":
              case "BODY":
                Mo(e);
                break;
              default:
                e.textContent = "";
            }
          }
          break;
        case 5:
        case 26:
        case 27:
        case 6:
        case 4:
        case 17:
          break;
        default:
          if ((e & 1024) !== 0) throw Error(o(163));
      }
      if (e = t.sibling, e !== null) {
        e.return = t.return, Be = e;
        break;
      }
      Be = t.return;
    }
  }
  function Ac(e, t, n) {
    var r = n.flags;
    switch (n.tag) {
      case 0:
      case 11:
      case 15:
        rn(e, n), r & 4 && Zr(5, n);
        break;
      case 1:
        if (rn(e, n), r & 4) if (e = n.stateNode, t === null) try {
          e.componentDidMount();
        } catch (i) {
          xe(n, n.return, i);
        }
        else {
          var a = Ln(n.type, t.memoizedProps);
          t = t.memoizedState;
          try {
            e.componentDidUpdate(a, t, e.__reactInternalSnapshotBeforeUpdate);
          } catch (i) {
            xe(n, n.return, i);
          }
        }
        r & 64 && jc(n), r & 512 && Jr(n, n.return);
        break;
      case 3:
        if (rn(e, n), r & 64 && (e = n.updateQueue, e !== null)) {
          if (t = null, n.child !== null) switch (n.child.tag) {
            case 27:
            case 5:
              t = n.child.stateNode;
              break;
            case 1:
              t = n.child.stateNode;
          }
          try {
            hu(e, t);
          } catch (i) {
            xe(n, n.return, i);
          }
        }
        break;
      case 27:
        t === null && r & 4 && Lc(n);
      case 26:
      case 5:
        rn(e, n), t === null && r & 4 && zc(n), r & 512 && Jr(n, n.return);
        break;
      case 12:
        rn(e, n);
        break;
      case 13:
        rn(e, n), r & 4 && Fc(e, n), r & 64 && (e = n.memoizedState, e !== null && (e = e.dehydrated, e !== null && (n = Bp.bind(null, n), lh(e, n))));
        break;
      case 22:
        if (r = n.memoizedState !== null || Bt, !r) {
          t = t !== null && t.memoizedState !== null || Te, a = Bt;
          var l = Te;
          Bt = r, (Te = t) && !l ? an(e, n, (n.subtreeFlags & 8772) !== 0) : rn(e, n), Bt = a, Te = l;
        }
        break;
      case 30:
        break;
      default:
        rn(e, n);
    }
  }
  function Rc(e) {
    var t = e.alternate;
    t !== null && (e.alternate = null, Rc(t)), e.child = null, e.deletions = null, e.sibling = null, e.tag === 5 && (t = e.stateNode, t !== null && Wl(t)), e.stateNode = null, e.return = null, e.dependencies = null, e.memoizedProps = null, e.memoizedState = null, e.pendingProps = null, e.stateNode = null, e.updateQueue = null;
  }
  var je = null, Je = false;
  function Wt(e, t, n) {
    for (n = n.child; n !== null; ) Oc(e, t, n), n = n.sibling;
  }
  function Oc(e, t, n) {
    if (nt && typeof nt.onCommitFiberUnmount == "function") try {
      nt.onCommitFiberUnmount(xr, n);
    } catch {
    }
    switch (n.tag) {
      case 26:
        Te || Et(n, t), Wt(e, t, n), n.memoizedState ? n.memoizedState.count-- : n.stateNode && (n = n.stateNode, n.parentNode.removeChild(n));
        break;
      case 27:
        Te || Et(n, t);
        var r = je, a = Je;
        dn(n.type) && (je = n.stateNode, Je = false), Wt(e, t, n), sa(n.stateNode), je = r, Je = a;
        break;
      case 5:
        Te || Et(n, t);
      case 6:
        if (r = je, a = Je, je = null, Wt(e, t, n), je = r, Je = a, je !== null) if (Je) try {
          (je.nodeType === 9 ? je.body : je.nodeName === "HTML" ? je.ownerDocument.body : je).removeChild(n.stateNode);
        } catch (l) {
          xe(n, t, l);
        }
        else try {
          je.removeChild(n.stateNode);
        } catch (l) {
          xe(n, t, l);
        }
        break;
      case 18:
        je !== null && (Je ? (e = je, Sd(e.nodeType === 9 ? e.body : e.nodeName === "HTML" ? e.ownerDocument.body : e, n.stateNode), ga(e)) : Sd(je, n.stateNode));
        break;
      case 4:
        r = je, a = Je, je = n.stateNode.containerInfo, Je = true, Wt(e, t, n), je = r, Je = a;
        break;
      case 0:
      case 11:
      case 14:
      case 15:
        Te || nn(2, n, t), Te || nn(4, n, t), Wt(e, t, n);
        break;
      case 1:
        Te || (Et(n, t), r = n.stateNode, typeof r.componentWillUnmount == "function" && Cc(n, t, r)), Wt(e, t, n);
        break;
      case 21:
        Wt(e, t, n);
        break;
      case 22:
        Te = (r = Te) || n.memoizedState !== null, Wt(e, t, n), Te = r;
        break;
      default:
        Wt(e, t, n);
    }
  }
  function Fc(e, t) {
    if (t.memoizedState === null && (e = t.alternate, e !== null && (e = e.memoizedState, e !== null && (e = e.dehydrated, e !== null)))) try {
      ga(e);
    } catch (n) {
      xe(t, t.return, n);
    }
  }
  function Ap(e) {
    switch (e.tag) {
      case 13:
      case 19:
        var t = e.stateNode;
        return t === null && (t = e.stateNode = new Tc()), t;
      case 22:
        return e = e.stateNode, t = e._retryCache, t === null && (t = e._retryCache = new Tc()), t;
      default:
        throw Error(o(435, e.tag));
    }
  }
  function co(e, t) {
    var n = Ap(e);
    t.forEach(function(r) {
      var a = Wp.bind(null, e, r);
      n.has(r) || (n.add(r), r.then(a, a));
    });
  }
  function it(e, t) {
    var n = t.deletions;
    if (n !== null) for (var r = 0; r < n.length; r++) {
      var a = n[r], l = e, i = t, u = i;
      e: for (; u !== null; ) {
        switch (u.tag) {
          case 27:
            if (dn(u.type)) {
              je = u.stateNode, Je = false;
              break e;
            }
            break;
          case 5:
            je = u.stateNode, Je = false;
            break e;
          case 3:
          case 4:
            je = u.stateNode.containerInfo, Je = true;
            break e;
        }
        u = u.return;
      }
      if (je === null) throw Error(o(160));
      Oc(l, i, a), je = null, Je = false, l = a.alternate, l !== null && (l.return = null), a.return = null;
    }
    if (t.subtreeFlags & 13878) for (t = t.child; t !== null; ) Dc(t, e), t = t.sibling;
  }
  var wt = null;
  function Dc(e, t) {
    var n = e.alternate, r = e.flags;
    switch (e.tag) {
      case 0:
      case 11:
      case 14:
      case 15:
        it(t, e), ot(e), r & 4 && (nn(3, e, e.return), Zr(3, e), nn(5, e, e.return));
        break;
      case 1:
        it(t, e), ot(e), r & 512 && (Te || n === null || Et(n, n.return)), r & 64 && Bt && (e = e.updateQueue, e !== null && (r = e.callbacks, r !== null && (n = e.shared.hiddenCallbacks, e.shared.hiddenCallbacks = n === null ? r : n.concat(r))));
        break;
      case 26:
        var a = wt;
        if (it(t, e), ot(e), r & 512 && (Te || n === null || Et(n, n.return)), r & 4) {
          var l = n !== null ? n.memoizedState : null;
          if (r = e.memoizedState, n === null) if (r === null) if (e.stateNode === null) {
            e: {
              r = e.type, n = e.memoizedProps, a = a.ownerDocument || a;
              t: switch (r) {
                case "title":
                  l = a.getElementsByTagName("title")[0], (!l || l[Sr] || l[Ke] || l.namespaceURI === "http://www.w3.org/2000/svg" || l.hasAttribute("itemprop")) && (l = a.createElement(r), a.head.insertBefore(l, a.querySelector("head > title"))), qe(l, r, n), l[Ke] = e, Ie(l), r = l;
                  break e;
                case "link":
                  var i = Ld("link", "href", a).get(r + (n.href || ""));
                  if (i) {
                    for (var u = 0; u < i.length; u++) if (l = i[u], l.getAttribute("href") === (n.href == null || n.href === "" ? null : n.href) && l.getAttribute("rel") === (n.rel == null ? null : n.rel) && l.getAttribute("title") === (n.title == null ? null : n.title) && l.getAttribute("crossorigin") === (n.crossOrigin == null ? null : n.crossOrigin)) {
                      i.splice(u, 1);
                      break t;
                    }
                  }
                  l = a.createElement(r), qe(l, r, n), a.head.appendChild(l);
                  break;
                case "meta":
                  if (i = Ld("meta", "content", a).get(r + (n.content || ""))) {
                    for (u = 0; u < i.length; u++) if (l = i[u], l.getAttribute("content") === (n.content == null ? null : "" + n.content) && l.getAttribute("name") === (n.name == null ? null : n.name) && l.getAttribute("property") === (n.property == null ? null : n.property) && l.getAttribute("http-equiv") === (n.httpEquiv == null ? null : n.httpEquiv) && l.getAttribute("charset") === (n.charSet == null ? null : n.charSet)) {
                      i.splice(u, 1);
                      break t;
                    }
                  }
                  l = a.createElement(r), qe(l, r, n), a.head.appendChild(l);
                  break;
                default:
                  throw Error(o(468, r));
              }
              l[Ke] = e, Ie(l), r = l;
            }
            e.stateNode = r;
          } else Td(a, e.type, e.stateNode);
          else e.stateNode = Pd(a, r, e.memoizedProps);
          else l !== r ? (l === null ? n.stateNode !== null && (n = n.stateNode, n.parentNode.removeChild(n)) : l.count--, r === null ? Td(a, e.type, e.stateNode) : Pd(a, r, e.memoizedProps)) : r === null && e.stateNode !== null && io(e, e.memoizedProps, n.memoizedProps);
        }
        break;
      case 27:
        it(t, e), ot(e), r & 512 && (Te || n === null || Et(n, n.return)), n !== null && r & 4 && io(e, e.memoizedProps, n.memoizedProps);
        break;
      case 5:
        if (it(t, e), ot(e), r & 512 && (Te || n === null || Et(n, n.return)), e.flags & 32) {
          a = e.stateNode;
          try {
            Wn(a, "");
          } catch (b) {
            xe(e, e.return, b);
          }
        }
        r & 4 && e.stateNode != null && (a = e.memoizedProps, io(e, a, n !== null ? n.memoizedProps : a)), r & 1024 && (uo = true);
        break;
      case 6:
        if (it(t, e), ot(e), r & 4) {
          if (e.stateNode === null) throw Error(o(162));
          r = e.memoizedProps, n = e.stateNode;
          try {
            n.nodeValue = r;
          } catch (b) {
            xe(e, e.return, b);
          }
        }
        break;
      case 3:
        if (Nl = null, a = wt, wt = kl(t.containerInfo), it(t, e), wt = a, ot(e), r & 4 && n !== null && n.memoizedState.isDehydrated) try {
          ga(t.containerInfo);
        } catch (b) {
          xe(e, e.return, b);
        }
        uo && (uo = false, Mc(e));
        break;
      case 4:
        r = wt, wt = kl(e.stateNode.containerInfo), it(t, e), ot(e), wt = r;
        break;
      case 12:
        it(t, e), ot(e);
        break;
      case 13:
        it(t, e), ot(e), e.child.flags & 8192 && e.memoizedState !== null != (n !== null && n.memoizedState !== null) && (yo = St()), r & 4 && (r = e.updateQueue, r !== null && (e.updateQueue = null, co(e, r)));
        break;
      case 22:
        a = e.memoizedState !== null;
        var d = n !== null && n.memoizedState !== null, y = Bt, w = Te;
        if (Bt = y || a, Te = w || d, it(t, e), Te = w, Bt = y, ot(e), r & 8192) e: for (t = e.stateNode, t._visibility = a ? t._visibility & -2 : t._visibility | 1, a && (n === null || d || Bt || Te || Tn(e)), n = null, t = e; ; ) {
          if (t.tag === 5 || t.tag === 26) {
            if (n === null) {
              d = n = t;
              try {
                if (l = d.stateNode, a) i = l.style, typeof i.setProperty == "function" ? i.setProperty("display", "none", "important") : i.display = "none";
                else {
                  u = d.stateNode;
                  var E = d.memoizedProps.style, v = E != null && E.hasOwnProperty("display") ? E.display : null;
                  u.style.display = v == null || typeof v == "boolean" ? "" : ("" + v).trim();
                }
              } catch (b) {
                xe(d, d.return, b);
              }
            }
          } else if (t.tag === 6) {
            if (n === null) {
              d = t;
              try {
                d.stateNode.nodeValue = a ? "" : d.memoizedProps;
              } catch (b) {
                xe(d, d.return, b);
              }
            }
          } else if ((t.tag !== 22 && t.tag !== 23 || t.memoizedState === null || t === e) && t.child !== null) {
            t.child.return = t, t = t.child;
            continue;
          }
          if (t === e) break e;
          for (; t.sibling === null; ) {
            if (t.return === null || t.return === e) break e;
            n === t && (n = null), t = t.return;
          }
          n === t && (n = null), t.sibling.return = t.return, t = t.sibling;
        }
        r & 4 && (r = e.updateQueue, r !== null && (n = r.retryQueue, n !== null && (r.retryQueue = null, co(e, n))));
        break;
      case 19:
        it(t, e), ot(e), r & 4 && (r = e.updateQueue, r !== null && (e.updateQueue = null, co(e, r)));
        break;
      case 30:
        break;
      case 21:
        break;
      default:
        it(t, e), ot(e);
    }
  }
  function ot(e) {
    var t = e.flags;
    if (t & 2) {
      try {
        for (var n, r = e.return; r !== null; ) {
          if (Pc(r)) {
            n = r;
            break;
          }
          r = r.return;
        }
        if (n == null) throw Error(o(160));
        switch (n.tag) {
          case 27:
            var a = n.stateNode, l = oo(e);
            ul(e, l, a);
            break;
          case 5:
            var i = n.stateNode;
            n.flags & 32 && (Wn(i, ""), n.flags &= -33);
            var u = oo(e);
            ul(e, u, i);
            break;
          case 3:
          case 4:
            var d = n.stateNode.containerInfo, y = oo(e);
            so(e, y, d);
            break;
          default:
            throw Error(o(161));
        }
      } catch (w) {
        xe(e, e.return, w);
      }
      e.flags &= -3;
    }
    t & 4096 && (e.flags &= -4097);
  }
  function Mc(e) {
    if (e.subtreeFlags & 1024) for (e = e.child; e !== null; ) {
      var t = e;
      Mc(t), t.tag === 5 && t.flags & 1024 && t.stateNode.reset(), e = e.sibling;
    }
  }
  function rn(e, t) {
    if (t.subtreeFlags & 8772) for (t = t.child; t !== null; ) Ac(e, t.alternate, t), t = t.sibling;
  }
  function Tn(e) {
    for (e = e.child; e !== null; ) {
      var t = e;
      switch (t.tag) {
        case 0:
        case 11:
        case 14:
        case 15:
          nn(4, t, t.return), Tn(t);
          break;
        case 1:
          Et(t, t.return);
          var n = t.stateNode;
          typeof n.componentWillUnmount == "function" && Cc(t, t.return, n), Tn(t);
          break;
        case 27:
          sa(t.stateNode);
        case 26:
        case 5:
          Et(t, t.return), Tn(t);
          break;
        case 22:
          t.memoizedState === null && Tn(t);
          break;
        case 30:
          Tn(t);
          break;
        default:
          Tn(t);
      }
      e = e.sibling;
    }
  }
  function an(e, t, n) {
    for (n = n && (t.subtreeFlags & 8772) !== 0, t = t.child; t !== null; ) {
      var r = t.alternate, a = e, l = t, i = l.flags;
      switch (l.tag) {
        case 0:
        case 11:
        case 15:
          an(a, l, n), Zr(4, l);
          break;
        case 1:
          if (an(a, l, n), r = l, a = r.stateNode, typeof a.componentDidMount == "function") try {
            a.componentDidMount();
          } catch (y) {
            xe(r, r.return, y);
          }
          if (r = l, a = r.updateQueue, a !== null) {
            var u = r.stateNode;
            try {
              var d = a.shared.hiddenCallbacks;
              if (d !== null) for (a.shared.hiddenCallbacks = null, a = 0; a < d.length; a++) pu(d[a], u);
            } catch (y) {
              xe(r, r.return, y);
            }
          }
          n && i & 64 && jc(l), Jr(l, l.return);
          break;
        case 27:
          Lc(l);
        case 26:
        case 5:
          an(a, l, n), n && r === null && i & 4 && zc(l), Jr(l, l.return);
          break;
        case 12:
          an(a, l, n);
          break;
        case 13:
          an(a, l, n), n && i & 4 && Fc(a, l);
          break;
        case 22:
          l.memoizedState === null && an(a, l, n), Jr(l, l.return);
          break;
        case 30:
          break;
        default:
          an(a, l, n);
      }
      t = t.sibling;
    }
  }
  function fo(e, t) {
    var n = null;
    e !== null && e.memoizedState !== null && e.memoizedState.cachePool !== null && (n = e.memoizedState.cachePool.pool), e = null, t.memoizedState !== null && t.memoizedState.cachePool !== null && (e = t.memoizedState.cachePool.pool), e !== n && (e != null && e.refCount++, n != null && Mr(n));
  }
  function po(e, t) {
    e = null, t.alternate !== null && (e = t.alternate.memoizedState.cache), t = t.memoizedState.cache, t !== e && (t.refCount++, e != null && Mr(e));
  }
  function _t(e, t, n, r) {
    if (t.subtreeFlags & 10256) for (t = t.child; t !== null; ) Ic(e, t, n, r), t = t.sibling;
  }
  function Ic(e, t, n, r) {
    var a = t.flags;
    switch (t.tag) {
      case 0:
      case 11:
      case 15:
        _t(e, t, n, r), a & 2048 && Zr(9, t);
        break;
      case 1:
        _t(e, t, n, r);
        break;
      case 3:
        _t(e, t, n, r), a & 2048 && (e = null, t.alternate !== null && (e = t.alternate.memoizedState.cache), t = t.memoizedState.cache, t !== e && (t.refCount++, e != null && Mr(e)));
        break;
      case 12:
        if (a & 2048) {
          _t(e, t, n, r), e = t.stateNode;
          try {
            var l = t.memoizedProps, i = l.id, u = l.onPostCommit;
            typeof u == "function" && u(i, t.alternate === null ? "mount" : "update", e.passiveEffectDuration, -0);
          } catch (d) {
            xe(t, t.return, d);
          }
        } else _t(e, t, n, r);
        break;
      case 13:
        _t(e, t, n, r);
        break;
      case 23:
        break;
      case 22:
        l = t.stateNode, i = t.alternate, t.memoizedState !== null ? l._visibility & 2 ? _t(e, t, n, r) : ea(e, t) : l._visibility & 2 ? _t(e, t, n, r) : (l._visibility |= 2, or(e, t, n, r, (t.subtreeFlags & 10256) !== 0)), a & 2048 && fo(i, t);
        break;
      case 24:
        _t(e, t, n, r), a & 2048 && po(t.alternate, t);
        break;
      default:
        _t(e, t, n, r);
    }
  }
  function or(e, t, n, r, a) {
    for (a = a && (t.subtreeFlags & 10256) !== 0, t = t.child; t !== null; ) {
      var l = e, i = t, u = n, d = r, y = i.flags;
      switch (i.tag) {
        case 0:
        case 11:
        case 15:
          or(l, i, u, d, a), Zr(8, i);
          break;
        case 23:
          break;
        case 22:
          var w = i.stateNode;
          i.memoizedState !== null ? w._visibility & 2 ? or(l, i, u, d, a) : ea(l, i) : (w._visibility |= 2, or(l, i, u, d, a)), a && y & 2048 && fo(i.alternate, i);
          break;
        case 24:
          or(l, i, u, d, a), a && y & 2048 && po(i.alternate, i);
          break;
        default:
          or(l, i, u, d, a);
      }
      t = t.sibling;
    }
  }
  function ea(e, t) {
    if (t.subtreeFlags & 10256) for (t = t.child; t !== null; ) {
      var n = e, r = t, a = r.flags;
      switch (r.tag) {
        case 22:
          ea(n, r), a & 2048 && fo(r.alternate, r);
          break;
        case 24:
          ea(n, r), a & 2048 && po(r.alternate, r);
          break;
        default:
          ea(n, r);
      }
      t = t.sibling;
    }
  }
  var ta = 8192;
  function sr(e) {
    if (e.subtreeFlags & ta) for (e = e.child; e !== null; ) Uc(e), e = e.sibling;
  }
  function Uc(e) {
    switch (e.tag) {
      case 26:
        sr(e), e.flags & ta && e.memoizedState !== null && vh(wt, e.memoizedState, e.memoizedProps);
        break;
      case 5:
        sr(e);
        break;
      case 3:
      case 4:
        var t = wt;
        wt = kl(e.stateNode.containerInfo), sr(e), wt = t;
        break;
      case 22:
        e.memoizedState === null && (t = e.alternate, t !== null && t.memoizedState !== null ? (t = ta, ta = 16777216, sr(e), ta = t) : sr(e));
        break;
      default:
        sr(e);
    }
  }
  function Bc(e) {
    var t = e.alternate;
    if (t !== null && (e = t.child, e !== null)) {
      t.child = null;
      do
        t = e.sibling, e.sibling = null, e = t;
      while (e !== null);
    }
  }
  function na(e) {
    var t = e.deletions;
    if ((e.flags & 16) !== 0) {
      if (t !== null) for (var n = 0; n < t.length; n++) {
        var r = t[n];
        Be = r, Hc(r, e);
      }
      Bc(e);
    }
    if (e.subtreeFlags & 10256) for (e = e.child; e !== null; ) Wc(e), e = e.sibling;
  }
  function Wc(e) {
    switch (e.tag) {
      case 0:
      case 11:
      case 15:
        na(e), e.flags & 2048 && nn(9, e, e.return);
        break;
      case 3:
        na(e);
        break;
      case 12:
        na(e);
        break;
      case 22:
        var t = e.stateNode;
        e.memoizedState !== null && t._visibility & 2 && (e.return === null || e.return.tag !== 13) ? (t._visibility &= -3, cl(e)) : na(e);
        break;
      default:
        na(e);
    }
  }
  function cl(e) {
    var t = e.deletions;
    if ((e.flags & 16) !== 0) {
      if (t !== null) for (var n = 0; n < t.length; n++) {
        var r = t[n];
        Be = r, Hc(r, e);
      }
      Bc(e);
    }
    for (e = e.child; e !== null; ) {
      switch (t = e, t.tag) {
        case 0:
        case 11:
        case 15:
          nn(8, t, t.return), cl(t);
          break;
        case 22:
          n = t.stateNode, n._visibility & 2 && (n._visibility &= -3, cl(t));
          break;
        default:
          cl(t);
      }
      e = e.sibling;
    }
  }
  function Hc(e, t) {
    for (; Be !== null; ) {
      var n = Be;
      switch (n.tag) {
        case 0:
        case 11:
        case 15:
          nn(8, n, t);
          break;
        case 23:
        case 22:
          if (n.memoizedState !== null && n.memoizedState.cachePool !== null) {
            var r = n.memoizedState.cachePool.pool;
            r != null && r.refCount++;
          }
          break;
        case 24:
          Mr(n.memoizedState.cache);
      }
      if (r = n.child, r !== null) r.return = n, Be = r;
      else e: for (n = e; Be !== null; ) {
        r = Be;
        var a = r.sibling, l = r.return;
        if (Rc(r), r === n) {
          Be = null;
          break e;
        }
        if (a !== null) {
          a.return = l, Be = a;
          break e;
        }
        Be = l;
      }
    }
  }
  var Rp = { getCacheForType: function(e) {
    var t = Ve(De), n = t.data.get(e);
    return n === void 0 && (n = e(), t.data.set(e, n)), n;
  } }, Op = typeof WeakMap == "function" ? WeakMap : Map, he = 0, Se = null, ne = null, le = 0, me = 0, st = null, ln = false, ur = false, ho = false, Ht = 0, Pe = 0, on = 0, An = 0, mo = 0, yt = 0, cr = 0, ra = null, et = null, go = false, yo = 0, dl = 1 / 0, fl = null, sn = null, $e = 0, un = null, dr = null, fr = 0, vo = 0, bo = null, $c = null, aa = 0, xo = null;
  function ut() {
    if ((he & 2) !== 0 && le !== 0) return le & -le;
    if (x.T !== null) {
      var e = Jn;
      return e !== 0 ? e : jo();
    }
    return is();
  }
  function qc() {
    yt === 0 && (yt = (le & 536870912) === 0 || de ? ns() : 536870912);
    var e = gt.current;
    return e !== null && (e.flags |= 32), yt;
  }
  function ct(e, t, n) {
    (e === Se && (me === 2 || me === 9) || e.cancelPendingCommit !== null) && (pr(e, 0), cn(e, le, yt, false)), kr(e, n), ((he & 2) === 0 || e !== Se) && (e === Se && ((he & 2) === 0 && (An |= n), Pe === 4 && cn(e, le, yt, false)), jt(e));
  }
  function Kc(e, t, n) {
    if ((he & 6) !== 0) throw Error(o(327));
    var r = !n && (t & 124) === 0 && (t & e.expiredLanes) === 0 || wr(e, t), a = r ? Mp(e, t) : So(e, t, true), l = r;
    do {
      if (a === 0) {
        ur && !r && cn(e, t, 0, false);
        break;
      } else {
        if (n = e.current.alternate, l && !Fp(n)) {
          a = So(e, t, false), l = false;
          continue;
        }
        if (a === 2) {
          if (l = t, e.errorRecoveryDisabledLanes & l) var i = 0;
          else i = e.pendingLanes & -536870913, i = i !== 0 ? i : i & 536870912 ? 536870912 : 0;
          if (i !== 0) {
            t = i;
            e: {
              var u = e;
              a = ra;
              var d = u.current.memoizedState.isDehydrated;
              if (d && (pr(u, i).flags |= 256), i = So(u, i, false), i !== 2) {
                if (ho && !d) {
                  u.errorRecoveryDisabledLanes |= l, An |= l, a = 4;
                  break e;
                }
                l = et, et = a, l !== null && (et === null ? et = l : et.push.apply(et, l));
              }
              a = i;
            }
            if (l = false, a !== 2) continue;
          }
        }
        if (a === 1) {
          pr(e, 0), cn(e, t, 0, true);
          break;
        }
        e: {
          switch (r = e, l = a, l) {
            case 0:
            case 1:
              throw Error(o(345));
            case 4:
              if ((t & 4194048) !== t) break;
            case 6:
              cn(r, t, yt, !ln);
              break e;
            case 2:
              et = null;
              break;
            case 3:
            case 5:
              break;
            default:
              throw Error(o(329));
          }
          if ((t & 62914560) === t && (a = yo + 300 - St(), 10 < a)) {
            if (cn(r, t, yt, !ln), Sa(r, 0, true) !== 0) break e;
            r.timeoutHandle = wd(Vc.bind(null, r, n, et, fl, go, t, yt, An, cr, ln, l, 2, -0, 0), a);
            break e;
          }
          Vc(r, n, et, fl, go, t, yt, An, cr, ln, l, 0, -0, 0);
        }
      }
      break;
    } while (true);
    jt(e);
  }
  function Vc(e, t, n, r, a, l, i, u, d, y, w, E, v, b) {
    if (e.timeoutHandle = -1, E = t.subtreeFlags, (E & 8192 || (E & 16785408) === 16785408) && (da = { stylesheets: null, count: 0, unsuspend: yh }, Uc(t), E = bh(), E !== null)) {
      e.cancelPendingCommit = E(ed.bind(null, e, t, l, n, r, a, i, u, d, w, 1, v, b)), cn(e, l, i, !y);
      return;
    }
    ed(e, t, l, n, r, a, i, u, d);
  }
  function Fp(e) {
    for (var t = e; ; ) {
      var n = t.tag;
      if ((n === 0 || n === 11 || n === 15) && t.flags & 16384 && (n = t.updateQueue, n !== null && (n = n.stores, n !== null))) for (var r = 0; r < n.length; r++) {
        var a = n[r], l = a.getSnapshot;
        a = a.value;
        try {
          if (!at(l(), a)) return false;
        } catch {
          return false;
        }
      }
      if (n = t.child, t.subtreeFlags & 16384 && n !== null) n.return = t, t = n;
      else {
        if (t === e) break;
        for (; t.sibling === null; ) {
          if (t.return === null || t.return === e) return true;
          t = t.return;
        }
        t.sibling.return = t.return, t = t.sibling;
      }
    }
    return true;
  }
  function cn(e, t, n, r) {
    t &= ~mo, t &= ~An, e.suspendedLanes |= t, e.pingedLanes &= ~t, r && (e.warmLanes |= t), r = e.expirationTimes;
    for (var a = t; 0 < a; ) {
      var l = 31 - rt(a), i = 1 << l;
      r[l] = -1, a &= ~i;
    }
    n !== 0 && as(e, n, t);
  }
  function pl() {
    return (he & 6) === 0 ? (la(0), false) : true;
  }
  function wo() {
    if (ne !== null) {
      if (me === 0) var e = ne.return;
      else e = ne, Rt = jn = null, Di(e), lr = null, Yr = 0, e = ne;
      for (; e !== null; ) _c(e.alternate, e), e = e.return;
      ne = null;
    }
  }
  function pr(e, t) {
    var n = e.timeoutHandle;
    n !== -1 && (e.timeoutHandle = -1, eh(n)), n = e.cancelPendingCommit, n !== null && (e.cancelPendingCommit = null, n()), wo(), Se = e, ne = n = Lt(e.current, null), le = t, me = 0, st = null, ln = false, ur = wr(e, t), ho = false, cr = yt = mo = An = on = Pe = 0, et = ra = null, go = false, (t & 8) !== 0 && (t |= t & 32);
    var r = e.entangledLanes;
    if (r !== 0) for (e = e.entanglements, r &= t; 0 < r; ) {
      var a = 31 - rt(r), l = 1 << a;
      t |= e[a], r &= ~l;
    }
    return Ht = t, Oa(), n;
  }
  function Qc(e, t) {
    ee = null, x.H = el, t === Ur || t === $a ? (t = du(), me = 3) : t === su ? (t = du(), me = 4) : me = t === fc ? 8 : t !== null && typeof t == "object" && typeof t.then == "function" ? 6 : 1, st = t, ne === null && (Pe = 1, ll(e, ft(t, e.current)));
  }
  function Yc() {
    var e = x.H;
    return x.H = el, e === null ? el : e;
  }
  function Gc() {
    var e = x.A;
    return x.A = Rp, e;
  }
  function ko() {
    Pe = 4, ln || (le & 4194048) !== le && gt.current !== null || (ur = true), (on & 134217727) === 0 && (An & 134217727) === 0 || Se === null || cn(Se, le, yt, false);
  }
  function So(e, t, n) {
    var r = he;
    he |= 2;
    var a = Yc(), l = Gc();
    (Se !== e || le !== t) && (fl = null, pr(e, t)), t = false;
    var i = Pe;
    e: do
      try {
        if (me !== 0 && ne !== null) {
          var u = ne, d = st;
          switch (me) {
            case 8:
              wo(), i = 6;
              break e;
            case 3:
            case 2:
            case 9:
            case 6:
              gt.current === null && (t = true);
              var y = me;
              if (me = 0, st = null, hr(e, u, d, y), n && ur) {
                i = 0;
                break e;
              }
              break;
            default:
              y = me, me = 0, st = null, hr(e, u, d, y);
          }
        }
        Dp(), i = Pe;
        break;
      } catch (w) {
        Qc(e, w);
      }
    while (true);
    return t && e.shellSuspendCounter++, Rt = jn = null, he = r, x.H = a, x.A = l, ne === null && (Se = null, le = 0, Oa()), i;
  }
  function Dp() {
    for (; ne !== null; ) Xc(ne);
  }
  function Mp(e, t) {
    var n = he;
    he |= 2;
    var r = Yc(), a = Gc();
    Se !== e || le !== t ? (fl = null, dl = St() + 500, pr(e, t)) : ur = wr(e, t);
    e: do
      try {
        if (me !== 0 && ne !== null) {
          t = ne;
          var l = st;
          t: switch (me) {
            case 1:
              me = 0, st = null, hr(e, t, l, 1);
              break;
            case 2:
            case 9:
              if (uu(l)) {
                me = 0, st = null, Zc(t);
                break;
              }
              t = function() {
                me !== 2 && me !== 9 || Se !== e || (me = 7), jt(e);
              }, l.then(t, t);
              break e;
            case 3:
              me = 7;
              break e;
            case 4:
              me = 5;
              break e;
            case 7:
              uu(l) ? (me = 0, st = null, Zc(t)) : (me = 0, st = null, hr(e, t, l, 7));
              break;
            case 5:
              var i = null;
              switch (ne.tag) {
                case 26:
                  i = ne.memoizedState;
                case 5:
                case 27:
                  var u = ne;
                  if (!i || Ad(i)) {
                    me = 0, st = null;
                    var d = u.sibling;
                    if (d !== null) ne = d;
                    else {
                      var y = u.return;
                      y !== null ? (ne = y, hl(y)) : ne = null;
                    }
                    break t;
                  }
              }
              me = 0, st = null, hr(e, t, l, 5);
              break;
            case 6:
              me = 0, st = null, hr(e, t, l, 6);
              break;
            case 8:
              wo(), Pe = 6;
              break e;
            default:
              throw Error(o(462));
          }
        }
        Ip();
        break;
      } catch (w) {
        Qc(e, w);
      }
    while (true);
    return Rt = jn = null, x.H = r, x.A = a, he = n, ne !== null ? 0 : (Se = null, le = 0, Oa(), Pe);
  }
  function Ip() {
    for (; ne !== null && !of(); ) Xc(ne);
  }
  function Xc(e) {
    var t = Nc(e.alternate, e, Ht);
    e.memoizedProps = e.pendingProps, t === null ? hl(e) : ne = t;
  }
  function Zc(e) {
    var t = e, n = t.alternate;
    switch (t.tag) {
      case 15:
      case 0:
        t = vc(n, t, t.pendingProps, t.type, void 0, le);
        break;
      case 11:
        t = vc(n, t, t.pendingProps, t.type.render, t.ref, le);
        break;
      case 5:
        Di(t);
      default:
        _c(n, t), t = ne = Js(t, Ht), t = Nc(n, t, Ht);
    }
    e.memoizedProps = e.pendingProps, t === null ? hl(e) : ne = t;
  }
  function hr(e, t, n, r) {
    Rt = jn = null, Di(t), lr = null, Yr = 0;
    var a = t.return;
    try {
      if (Cp(e, a, t, n, le)) {
        Pe = 1, ll(e, ft(n, e.current)), ne = null;
        return;
      }
    } catch (l) {
      if (a !== null) throw ne = a, l;
      Pe = 1, ll(e, ft(n, e.current)), ne = null;
      return;
    }
    t.flags & 32768 ? (de || r === 1 ? e = true : ur || (le & 536870912) !== 0 ? e = false : (ln = e = true, (r === 2 || r === 9 || r === 3 || r === 6) && (r = gt.current, r !== null && r.tag === 13 && (r.flags |= 16384))), Jc(t, e)) : hl(t);
  }
  function hl(e) {
    var t = e;
    do {
      if ((t.flags & 32768) !== 0) {
        Jc(t, ln);
        return;
      }
      e = t.return;
      var n = Pp(t.alternate, t, Ht);
      if (n !== null) {
        ne = n;
        return;
      }
      if (t = t.sibling, t !== null) {
        ne = t;
        return;
      }
      ne = t = e;
    } while (t !== null);
    Pe === 0 && (Pe = 5);
  }
  function Jc(e, t) {
    do {
      var n = Lp(e.alternate, e);
      if (n !== null) {
        n.flags &= 32767, ne = n;
        return;
      }
      if (n = e.return, n !== null && (n.flags |= 32768, n.subtreeFlags = 0, n.deletions = null), !t && (e = e.sibling, e !== null)) {
        ne = e;
        return;
      }
      ne = e = n;
    } while (e !== null);
    Pe = 6, ne = null;
  }
  function ed(e, t, n, r, a, l, i, u, d) {
    e.cancelPendingCommit = null;
    do
      ml();
    while ($e !== 0);
    if ((he & 6) !== 0) throw Error(o(327));
    if (t !== null) {
      if (t === e.current) throw Error(o(177));
      if (l = t.lanes | t.childLanes, l |= fi, yf(e, n, l, i, u, d), e === Se && (ne = Se = null, le = 0), dr = t, un = e, fr = n, vo = l, bo = a, $c = r, (t.subtreeFlags & 10256) !== 0 || (t.flags & 10256) !== 0 ? (e.callbackNode = null, e.callbackPriority = 0, Hp(xa, function() {
        return ld(), null;
      })) : (e.callbackNode = null, e.callbackPriority = 0), r = (t.flags & 13878) !== 0, (t.subtreeFlags & 13878) !== 0 || r) {
        r = x.T, x.T = null, a = z.p, z.p = 2, i = he, he |= 4;
        try {
          Tp(e, t, n);
        } finally {
          he = i, z.p = a, x.T = r;
        }
      }
      $e = 1, td(), nd(), rd();
    }
  }
  function td() {
    if ($e === 1) {
      $e = 0;
      var e = un, t = dr, n = (t.flags & 13878) !== 0;
      if ((t.subtreeFlags & 13878) !== 0 || n) {
        n = x.T, x.T = null;
        var r = z.p;
        z.p = 2;
        var a = he;
        he |= 4;
        try {
          Dc(t, e);
          var l = Oo, i = Hs(e.containerInfo), u = l.focusedElem, d = l.selectionRange;
          if (i !== u && u && u.ownerDocument && Ws(u.ownerDocument.documentElement, u)) {
            if (d !== null && oi(u)) {
              var y = d.start, w = d.end;
              if (w === void 0 && (w = y), "selectionStart" in u) u.selectionStart = y, u.selectionEnd = Math.min(w, u.value.length);
              else {
                var E = u.ownerDocument || document, v = E && E.defaultView || window;
                if (v.getSelection) {
                  var b = v.getSelection(), Q = u.textContent.length, H = Math.min(d.start, Q), ve = d.end === void 0 ? H : Math.min(d.end, Q);
                  !b.extend && H > ve && (i = ve, ve = H, H = i);
                  var h = Bs(u, H), p = Bs(u, ve);
                  if (h && p && (b.rangeCount !== 1 || b.anchorNode !== h.node || b.anchorOffset !== h.offset || b.focusNode !== p.node || b.focusOffset !== p.offset)) {
                    var g = E.createRange();
                    g.setStart(h.node, h.offset), b.removeAllRanges(), H > ve ? (b.addRange(g), b.extend(p.node, p.offset)) : (g.setEnd(p.node, p.offset), b.addRange(g));
                  }
                }
              }
            }
            for (E = [], b = u; b = b.parentNode; ) b.nodeType === 1 && E.push({ element: b, left: b.scrollLeft, top: b.scrollTop });
            for (typeof u.focus == "function" && u.focus(), u = 0; u < E.length; u++) {
              var S = E[u];
              S.element.scrollLeft = S.left, S.element.scrollTop = S.top;
            }
          }
          jl = !!Ro, Oo = Ro = null;
        } finally {
          he = a, z.p = r, x.T = n;
        }
      }
      e.current = t, $e = 2;
    }
  }
  function nd() {
    if ($e === 2) {
      $e = 0;
      var e = un, t = dr, n = (t.flags & 8772) !== 0;
      if ((t.subtreeFlags & 8772) !== 0 || n) {
        n = x.T, x.T = null;
        var r = z.p;
        z.p = 2;
        var a = he;
        he |= 4;
        try {
          Ac(e, t.alternate, t);
        } finally {
          he = a, z.p = r, x.T = n;
        }
      }
      $e = 3;
    }
  }
  function rd() {
    if ($e === 4 || $e === 3) {
      $e = 0, sf();
      var e = un, t = dr, n = fr, r = $c;
      (t.subtreeFlags & 10256) !== 0 || (t.flags & 10256) !== 0 ? $e = 5 : ($e = 0, dr = un = null, ad(e, e.pendingLanes));
      var a = e.pendingLanes;
      if (a === 0 && (sn = null), Ul(n), t = t.stateNode, nt && typeof nt.onCommitFiberRoot == "function") try {
        nt.onCommitFiberRoot(xr, t, void 0, (t.current.flags & 128) === 128);
      } catch {
      }
      if (r !== null) {
        t = x.T, a = z.p, z.p = 2, x.T = null;
        try {
          for (var l = e.onRecoverableError, i = 0; i < r.length; i++) {
            var u = r[i];
            l(u.value, { componentStack: u.stack });
          }
        } finally {
          x.T = t, z.p = a;
        }
      }
      (fr & 3) !== 0 && ml(), jt(e), a = e.pendingLanes, (n & 4194090) !== 0 && (a & 42) !== 0 ? e === xo ? aa++ : (aa = 0, xo = e) : aa = 0, la(0);
    }
  }
  function ad(e, t) {
    (e.pooledCacheLanes &= t) === 0 && (t = e.pooledCache, t != null && (e.pooledCache = null, Mr(t)));
  }
  function ml(e) {
    return td(), nd(), rd(), ld();
  }
  function ld() {
    if ($e !== 5) return false;
    var e = un, t = vo;
    vo = 0;
    var n = Ul(fr), r = x.T, a = z.p;
    try {
      z.p = 32 > n ? 32 : n, x.T = null, n = bo, bo = null;
      var l = un, i = fr;
      if ($e = 0, dr = un = null, fr = 0, (he & 6) !== 0) throw Error(o(331));
      var u = he;
      if (he |= 4, Wc(l.current), Ic(l, l.current, i, n), he = u, la(0, false), nt && typeof nt.onPostCommitFiberRoot == "function") try {
        nt.onPostCommitFiberRoot(xr, l);
      } catch {
      }
      return true;
    } finally {
      z.p = a, x.T = r, ad(e, t);
    }
  }
  function id(e, t, n) {
    t = ft(n, t), t = Xi(e.stateNode, t, 2), e = Zt(e, t, 2), e !== null && (kr(e, 2), jt(e));
  }
  function xe(e, t, n) {
    if (e.tag === 3) id(e, e, n);
    else for (; t !== null; ) {
      if (t.tag === 3) {
        id(t, e, n);
        break;
      } else if (t.tag === 1) {
        var r = t.stateNode;
        if (typeof t.type.getDerivedStateFromError == "function" || typeof r.componentDidCatch == "function" && (sn === null || !sn.has(r))) {
          e = ft(n, e), n = cc(2), r = Zt(t, n, 2), r !== null && (dc(n, r, t, e), kr(r, 2), jt(r));
          break;
        }
      }
      t = t.return;
    }
  }
  function No(e, t, n) {
    var r = e.pingCache;
    if (r === null) {
      r = e.pingCache = new Op();
      var a = /* @__PURE__ */ new Set();
      r.set(t, a);
    } else a = r.get(t), a === void 0 && (a = /* @__PURE__ */ new Set(), r.set(t, a));
    a.has(n) || (ho = true, a.add(n), e = Up.bind(null, e, t, n), t.then(e, e));
  }
  function Up(e, t, n) {
    var r = e.pingCache;
    r !== null && r.delete(t), e.pingedLanes |= e.suspendedLanes & n, e.warmLanes &= ~n, Se === e && (le & n) === n && (Pe === 4 || Pe === 3 && (le & 62914560) === le && 300 > St() - yo ? (he & 2) === 0 && pr(e, 0) : mo |= n, cr === le && (cr = 0)), jt(e);
  }
  function od(e, t) {
    t === 0 && (t = rs()), e = Yn(e, t), e !== null && (kr(e, t), jt(e));
  }
  function Bp(e) {
    var t = e.memoizedState, n = 0;
    t !== null && (n = t.retryLane), od(e, n);
  }
  function Wp(e, t) {
    var n = 0;
    switch (e.tag) {
      case 13:
        var r = e.stateNode, a = e.memoizedState;
        a !== null && (n = a.retryLane);
        break;
      case 19:
        r = e.stateNode;
        break;
      case 22:
        r = e.stateNode._retryCache;
        break;
      default:
        throw Error(o(314));
    }
    r !== null && r.delete(t), od(e, n);
  }
  function Hp(e, t) {
    return Fl(e, t);
  }
  var gl = null, mr = null, Eo = false, yl = false, _o = false, Rn = 0;
  function jt(e) {
    e !== mr && e.next === null && (mr === null ? gl = mr = e : mr = mr.next = e), yl = true, Eo || (Eo = true, qp());
  }
  function la(e, t) {
    if (!_o && yl) {
      _o = true;
      do
        for (var n = false, r = gl; r !== null; ) {
          if (e !== 0) {
            var a = r.pendingLanes;
            if (a === 0) var l = 0;
            else {
              var i = r.suspendedLanes, u = r.pingedLanes;
              l = (1 << 31 - rt(42 | e) + 1) - 1, l &= a & ~(i & ~u), l = l & 201326741 ? l & 201326741 | 1 : l ? l | 2 : 0;
            }
            l !== 0 && (n = true, dd(r, l));
          } else l = le, l = Sa(r, r === Se ? l : 0, r.cancelPendingCommit !== null || r.timeoutHandle !== -1), (l & 3) === 0 || wr(r, l) || (n = true, dd(r, l));
          r = r.next;
        }
      while (n);
      _o = false;
    }
  }
  function $p() {
    sd();
  }
  function sd() {
    yl = Eo = false;
    var e = 0;
    Rn !== 0 && (Jp() && (e = Rn), Rn = 0);
    for (var t = St(), n = null, r = gl; r !== null; ) {
      var a = r.next, l = ud(r, t);
      l === 0 ? (r.next = null, n === null ? gl = a : n.next = a, a === null && (mr = n)) : (n = r, (e !== 0 || (l & 3) !== 0) && (yl = true)), r = a;
    }
    la(e);
  }
  function ud(e, t) {
    for (var n = e.suspendedLanes, r = e.pingedLanes, a = e.expirationTimes, l = e.pendingLanes & -62914561; 0 < l; ) {
      var i = 31 - rt(l), u = 1 << i, d = a[i];
      d === -1 ? ((u & n) === 0 || (u & r) !== 0) && (a[i] = gf(u, t)) : d <= t && (e.expiredLanes |= u), l &= ~u;
    }
    if (t = Se, n = le, n = Sa(e, e === t ? n : 0, e.cancelPendingCommit !== null || e.timeoutHandle !== -1), r = e.callbackNode, n === 0 || e === t && (me === 2 || me === 9) || e.cancelPendingCommit !== null) return r !== null && r !== null && Dl(r), e.callbackNode = null, e.callbackPriority = 0;
    if ((n & 3) === 0 || wr(e, n)) {
      if (t = n & -n, t === e.callbackPriority) return t;
      switch (r !== null && Dl(r), Ul(n)) {
        case 2:
        case 8:
          n = es;
          break;
        case 32:
          n = xa;
          break;
        case 268435456:
          n = ts;
          break;
        default:
          n = xa;
      }
      return r = cd.bind(null, e), n = Fl(n, r), e.callbackPriority = t, e.callbackNode = n, t;
    }
    return r !== null && r !== null && Dl(r), e.callbackPriority = 2, e.callbackNode = null, 2;
  }
  function cd(e, t) {
    if ($e !== 0 && $e !== 5) return e.callbackNode = null, e.callbackPriority = 0, null;
    var n = e.callbackNode;
    if (ml() && e.callbackNode !== n) return null;
    var r = le;
    return r = Sa(e, e === Se ? r : 0, e.cancelPendingCommit !== null || e.timeoutHandle !== -1), r === 0 ? null : (Kc(e, r, t), ud(e, St()), e.callbackNode != null && e.callbackNode === n ? cd.bind(null, e) : null);
  }
  function dd(e, t) {
    if (ml()) return null;
    Kc(e, t, true);
  }
  function qp() {
    th(function() {
      (he & 6) !== 0 ? Fl(Jo, $p) : sd();
    });
  }
  function jo() {
    return Rn === 0 && (Rn = ns()), Rn;
  }
  function fd(e) {
    return e == null || typeof e == "symbol" || typeof e == "boolean" ? null : typeof e == "function" ? e : Ca("" + e);
  }
  function pd(e, t) {
    var n = t.ownerDocument.createElement("input");
    return n.name = t.name, n.value = t.value, e.id && n.setAttribute("form", e.id), t.parentNode.insertBefore(n, t), e = new FormData(e), n.parentNode.removeChild(n), e;
  }
  function Kp(e, t, n, r, a) {
    if (t === "submit" && n && n.stateNode === a) {
      var l = fd((a[Ge] || null).action), i = r.submitter;
      i && (t = (t = i[Ge] || null) ? fd(t.formAction) : i.getAttribute("formAction"), t !== null && (l = t, i = null));
      var u = new Ta("action", "action", null, r, a);
      e.push({ event: u, listeners: [{ instance: null, listener: function() {
        if (r.defaultPrevented) {
          if (Rn !== 0) {
            var d = i ? pd(a, i) : new FormData(a);
            Ki(n, { pending: true, data: d, method: a.method, action: l }, null, d);
          }
        } else typeof l == "function" && (u.preventDefault(), d = i ? pd(a, i) : new FormData(a), Ki(n, { pending: true, data: d, method: a.method, action: l }, l, d));
      }, currentTarget: a }] });
    }
  }
  for (var Co = 0; Co < di.length; Co++) {
    var zo = di[Co], Vp = zo.toLowerCase(), Qp = zo[0].toUpperCase() + zo.slice(1);
    xt(Vp, "on" + Qp);
  }
  xt(Ks, "onAnimationEnd"), xt(Vs, "onAnimationIteration"), xt(Qs, "onAnimationStart"), xt("dblclick", "onDoubleClick"), xt("focusin", "onFocus"), xt("focusout", "onBlur"), xt(dp, "onTransitionRun"), xt(fp, "onTransitionStart"), xt(pp, "onTransitionCancel"), xt(Ys, "onTransitionEnd"), In("onMouseEnter", ["mouseout", "mouseover"]), In("onMouseLeave", ["mouseout", "mouseover"]), In("onPointerEnter", ["pointerout", "pointerover"]), In("onPointerLeave", ["pointerout", "pointerover"]), vn("onChange", "change click focusin focusout input keydown keyup selectionchange".split(" ")), vn("onSelect", "focusout contextmenu dragend focusin keydown keyup mousedown mouseup selectionchange".split(" ")), vn("onBeforeInput", ["compositionend", "keypress", "textInput", "paste"]), vn("onCompositionEnd", "compositionend focusout keydown keypress keyup mousedown".split(" ")), vn("onCompositionStart", "compositionstart focusout keydown keypress keyup mousedown".split(" ")), vn("onCompositionUpdate", "compositionupdate focusout keydown keypress keyup mousedown".split(" "));
  var ia = "abort canplay canplaythrough durationchange emptied encrypted ended error loadeddata loadedmetadata loadstart pause play playing progress ratechange resize seeked seeking stalled suspend timeupdate volumechange waiting".split(" "), Yp = new Set("beforetoggle cancel close invalid load scroll scrollend toggle".split(" ").concat(ia));
  function hd(e, t) {
    t = (t & 4) !== 0;
    for (var n = 0; n < e.length; n++) {
      var r = e[n], a = r.event;
      r = r.listeners;
      e: {
        var l = void 0;
        if (t) for (var i = r.length - 1; 0 <= i; i--) {
          var u = r[i], d = u.instance, y = u.currentTarget;
          if (u = u.listener, d !== l && a.isPropagationStopped()) break e;
          l = u, a.currentTarget = y;
          try {
            l(a);
          } catch (w) {
            al(w);
          }
          a.currentTarget = null, l = d;
        }
        else for (i = 0; i < r.length; i++) {
          if (u = r[i], d = u.instance, y = u.currentTarget, u = u.listener, d !== l && a.isPropagationStopped()) break e;
          l = u, a.currentTarget = y;
          try {
            l(a);
          } catch (w) {
            al(w);
          }
          a.currentTarget = null, l = d;
        }
      }
    }
  }
  function re(e, t) {
    var n = t[Bl];
    n === void 0 && (n = t[Bl] = /* @__PURE__ */ new Set());
    var r = e + "__bubble";
    n.has(r) || (md(t, e, 2, false), n.add(r));
  }
  function Po(e, t, n) {
    var r = 0;
    t && (r |= 4), md(n, e, r, t);
  }
  var vl = "_reactListening" + Math.random().toString(36).slice(2);
  function Lo(e) {
    if (!e[vl]) {
      e[vl] = true, ss.forEach(function(n) {
        n !== "selectionchange" && (Yp.has(n) || Po(n, false, e), Po(n, true, e));
      });
      var t = e.nodeType === 9 ? e : e.ownerDocument;
      t === null || t[vl] || (t[vl] = true, Po("selectionchange", false, t));
    }
  }
  function md(e, t, n, r) {
    switch (Id(t)) {
      case 2:
        var a = kh;
        break;
      case 8:
        a = Sh;
        break;
      default:
        a = qo;
    }
    n = a.bind(null, t, n, e), a = void 0, !Zl || t !== "touchstart" && t !== "touchmove" && t !== "wheel" || (a = true), r ? a !== void 0 ? e.addEventListener(t, n, { capture: true, passive: a }) : e.addEventListener(t, n, true) : a !== void 0 ? e.addEventListener(t, n, { passive: a }) : e.addEventListener(t, n, false);
  }
  function To(e, t, n, r, a) {
    var l = r;
    if ((t & 1) === 0 && (t & 2) === 0 && r !== null) e: for (; ; ) {
      if (r === null) return;
      var i = r.tag;
      if (i === 3 || i === 4) {
        var u = r.stateNode.containerInfo;
        if (u === a) break;
        if (i === 4) for (i = r.return; i !== null; ) {
          var d = i.tag;
          if ((d === 3 || d === 4) && i.stateNode.containerInfo === a) return;
          i = i.return;
        }
        for (; u !== null; ) {
          if (i = Fn(u), i === null) return;
          if (d = i.tag, d === 5 || d === 6 || d === 26 || d === 27) {
            r = l = i;
            continue e;
          }
          u = u.parentNode;
        }
      }
      r = r.return;
    }
    ks(function() {
      var y = l, w = Gl(n), E = [];
      e: {
        var v = Gs.get(e);
        if (v !== void 0) {
          var b = Ta, Q = e;
          switch (e) {
            case "keypress":
              if (Pa(n) === 0) break e;
            case "keydown":
            case "keyup":
              b = Hf;
              break;
            case "focusin":
              Q = "focus", b = ni;
              break;
            case "focusout":
              Q = "blur", b = ni;
              break;
            case "beforeblur":
            case "afterblur":
              b = ni;
              break;
            case "click":
              if (n.button === 2) break e;
            case "auxclick":
            case "dblclick":
            case "mousedown":
            case "mousemove":
            case "mouseup":
            case "mouseout":
            case "mouseover":
            case "contextmenu":
              b = Es;
              break;
            case "drag":
            case "dragend":
            case "dragenter":
            case "dragexit":
            case "dragleave":
            case "dragover":
            case "dragstart":
            case "drop":
              b = Lf;
              break;
            case "touchcancel":
            case "touchend":
            case "touchmove":
            case "touchstart":
              b = Kf;
              break;
            case Ks:
            case Vs:
            case Qs:
              b = Rf;
              break;
            case Ys:
              b = Qf;
              break;
            case "scroll":
            case "scrollend":
              b = zf;
              break;
            case "wheel":
              b = Gf;
              break;
            case "copy":
            case "cut":
            case "paste":
              b = Ff;
              break;
            case "gotpointercapture":
            case "lostpointercapture":
            case "pointercancel":
            case "pointerdown":
            case "pointermove":
            case "pointerout":
            case "pointerover":
            case "pointerup":
              b = js;
              break;
            case "toggle":
            case "beforetoggle":
              b = Zf;
          }
          var H = (t & 4) !== 0, ve = !H && (e === "scroll" || e === "scrollend"), h = H ? v !== null ? v + "Capture" : null : v;
          H = [];
          for (var p = y, g; p !== null; ) {
            var S = p;
            if (g = S.stateNode, S = S.tag, S !== 5 && S !== 26 && S !== 27 || g === null || h === null || (S = Er(p, h), S != null && H.push(oa(p, S, g))), ve) break;
            p = p.return;
          }
          0 < H.length && (v = new b(v, Q, null, n, w), E.push({ event: v, listeners: H }));
        }
      }
      if ((t & 7) === 0) {
        e: {
          if (v = e === "mouseover" || e === "pointerover", b = e === "mouseout" || e === "pointerout", v && n !== Yl && (Q = n.relatedTarget || n.fromElement) && (Fn(Q) || Q[On])) break e;
          if ((b || v) && (v = w.window === w ? w : (v = w.ownerDocument) ? v.defaultView || v.parentWindow : window, b ? (Q = n.relatedTarget || n.toElement, b = y, Q = Q ? Fn(Q) : null, Q !== null && (ve = k(Q), H = Q.tag, Q !== ve || H !== 5 && H !== 27 && H !== 6) && (Q = null)) : (b = null, Q = y), b !== Q)) {
            if (H = Es, S = "onMouseLeave", h = "onMouseEnter", p = "mouse", (e === "pointerout" || e === "pointerover") && (H = js, S = "onPointerLeave", h = "onPointerEnter", p = "pointer"), ve = b == null ? v : Nr(b), g = Q == null ? v : Nr(Q), v = new H(S, p + "leave", b, n, w), v.target = ve, v.relatedTarget = g, S = null, Fn(w) === y && (H = new H(h, p + "enter", Q, n, w), H.target = g, H.relatedTarget = ve, S = H), ve = S, b && Q) t: {
              for (H = b, h = Q, p = 0, g = H; g; g = gr(g)) p++;
              for (g = 0, S = h; S; S = gr(S)) g++;
              for (; 0 < p - g; ) H = gr(H), p--;
              for (; 0 < g - p; ) h = gr(h), g--;
              for (; p--; ) {
                if (H === h || h !== null && H === h.alternate) break t;
                H = gr(H), h = gr(h);
              }
              H = null;
            }
            else H = null;
            b !== null && gd(E, v, b, H, false), Q !== null && ve !== null && gd(E, ve, Q, H, true);
          }
        }
        e: {
          if (v = y ? Nr(y) : window, b = v.nodeName && v.nodeName.toLowerCase(), b === "select" || b === "input" && v.type === "file") var M = Os;
          else if (As(v)) if (Fs) M = sp;
          else {
            M = ip;
            var te = lp;
          }
          else b = v.nodeName, !b || b.toLowerCase() !== "input" || v.type !== "checkbox" && v.type !== "radio" ? y && Ql(y.elementType) && (M = Os) : M = op;
          if (M && (M = M(e, y))) {
            Rs(E, M, n, w);
            break e;
          }
          te && te(e, v, y), e === "focusout" && y && v.type === "number" && y.memoizedProps.value != null && Vl(v, "number", v.value);
        }
        switch (te = y ? Nr(y) : window, e) {
          case "focusin":
            (As(te) || te.contentEditable === "true") && (Kn = te, si = y, Ar = null);
            break;
          case "focusout":
            Ar = si = Kn = null;
            break;
          case "mousedown":
            ui = true;
            break;
          case "contextmenu":
          case "mouseup":
          case "dragend":
            ui = false, $s(E, n, w);
            break;
          case "selectionchange":
            if (cp) break;
          case "keydown":
          case "keyup":
            $s(E, n, w);
        }
        var I;
        if (ai) e: {
          switch (e) {
            case "compositionstart":
              var $ = "onCompositionStart";
              break e;
            case "compositionend":
              $ = "onCompositionEnd";
              break e;
            case "compositionupdate":
              $ = "onCompositionUpdate";
              break e;
          }
          $ = void 0;
        }
        else qn ? Ls(e, n) && ($ = "onCompositionEnd") : e === "keydown" && n.keyCode === 229 && ($ = "onCompositionStart");
        $ && (Cs && n.locale !== "ko" && (qn || $ !== "onCompositionStart" ? $ === "onCompositionEnd" && qn && (I = Ss()) : (Qt = w, Jl = "value" in Qt ? Qt.value : Qt.textContent, qn = true)), te = bl(y, $), 0 < te.length && ($ = new _s($, e, null, n, w), E.push({ event: $, listeners: te }), I ? $.data = I : (I = Ts(n), I !== null && ($.data = I)))), (I = ep ? tp(e, n) : np(e, n)) && ($ = bl(y, "onBeforeInput"), 0 < $.length && (te = new _s("onBeforeInput", "beforeinput", null, n, w), E.push({ event: te, listeners: $ }), te.data = I)), Kp(E, e, y, n, w);
      }
      hd(E, t);
    });
  }
  function oa(e, t, n) {
    return { instance: e, listener: t, currentTarget: n };
  }
  function bl(e, t) {
    for (var n = t + "Capture", r = []; e !== null; ) {
      var a = e, l = a.stateNode;
      if (a = a.tag, a !== 5 && a !== 26 && a !== 27 || l === null || (a = Er(e, n), a != null && r.unshift(oa(e, a, l)), a = Er(e, t), a != null && r.push(oa(e, a, l))), e.tag === 3) return r;
      e = e.return;
    }
    return [];
  }
  function gr(e) {
    if (e === null) return null;
    do
      e = e.return;
    while (e && e.tag !== 5 && e.tag !== 27);
    return e || null;
  }
  function gd(e, t, n, r, a) {
    for (var l = t._reactName, i = []; n !== null && n !== r; ) {
      var u = n, d = u.alternate, y = u.stateNode;
      if (u = u.tag, d !== null && d === r) break;
      u !== 5 && u !== 26 && u !== 27 || y === null || (d = y, a ? (y = Er(n, l), y != null && i.unshift(oa(n, y, d))) : a || (y = Er(n, l), y != null && i.push(oa(n, y, d)))), n = n.return;
    }
    i.length !== 0 && e.push({ event: t, listeners: i });
  }
  var Gp = /\r\n?/g, Xp = /\u0000|\uFFFD/g;
  function yd(e) {
    return (typeof e == "string" ? e : "" + e).replace(Gp, `
`).replace(Xp, "");
  }
  function vd(e, t) {
    return t = yd(t), yd(e) === t;
  }
  function xl() {
  }
  function ye(e, t, n, r, a, l) {
    switch (n) {
      case "children":
        typeof r == "string" ? t === "body" || t === "textarea" && r === "" || Wn(e, r) : (typeof r == "number" || typeof r == "bigint") && t !== "body" && Wn(e, "" + r);
        break;
      case "className":
        Ea(e, "class", r);
        break;
      case "tabIndex":
        Ea(e, "tabindex", r);
        break;
      case "dir":
      case "role":
      case "viewBox":
      case "width":
      case "height":
        Ea(e, n, r);
        break;
      case "style":
        xs(e, r, l);
        break;
      case "data":
        if (t !== "object") {
          Ea(e, "data", r);
          break;
        }
      case "src":
      case "href":
        if (r === "" && (t !== "a" || n !== "href")) {
          e.removeAttribute(n);
          break;
        }
        if (r == null || typeof r == "function" || typeof r == "symbol" || typeof r == "boolean") {
          e.removeAttribute(n);
          break;
        }
        r = Ca("" + r), e.setAttribute(n, r);
        break;
      case "action":
      case "formAction":
        if (typeof r == "function") {
          e.setAttribute(n, "javascript:throw new Error('A React form was unexpectedly submitted. If you called form.submit() manually, consider using form.requestSubmit() instead. If you\\'re trying to use event.stopPropagation() in a submit event handler, consider also calling event.preventDefault().')");
          break;
        } else typeof l == "function" && (n === "formAction" ? (t !== "input" && ye(e, t, "name", a.name, a, null), ye(e, t, "formEncType", a.formEncType, a, null), ye(e, t, "formMethod", a.formMethod, a, null), ye(e, t, "formTarget", a.formTarget, a, null)) : (ye(e, t, "encType", a.encType, a, null), ye(e, t, "method", a.method, a, null), ye(e, t, "target", a.target, a, null)));
        if (r == null || typeof r == "symbol" || typeof r == "boolean") {
          e.removeAttribute(n);
          break;
        }
        r = Ca("" + r), e.setAttribute(n, r);
        break;
      case "onClick":
        r != null && (e.onclick = xl);
        break;
      case "onScroll":
        r != null && re("scroll", e);
        break;
      case "onScrollEnd":
        r != null && re("scrollend", e);
        break;
      case "dangerouslySetInnerHTML":
        if (r != null) {
          if (typeof r != "object" || !("__html" in r)) throw Error(o(61));
          if (n = r.__html, n != null) {
            if (a.children != null) throw Error(o(60));
            e.innerHTML = n;
          }
        }
        break;
      case "multiple":
        e.multiple = r && typeof r != "function" && typeof r != "symbol";
        break;
      case "muted":
        e.muted = r && typeof r != "function" && typeof r != "symbol";
        break;
      case "suppressContentEditableWarning":
      case "suppressHydrationWarning":
      case "defaultValue":
      case "defaultChecked":
      case "innerHTML":
      case "ref":
        break;
      case "autoFocus":
        break;
      case "xlinkHref":
        if (r == null || typeof r == "function" || typeof r == "boolean" || typeof r == "symbol") {
          e.removeAttribute("xlink:href");
          break;
        }
        n = Ca("" + r), e.setAttributeNS("http://www.w3.org/1999/xlink", "xlink:href", n);
        break;
      case "contentEditable":
      case "spellCheck":
      case "draggable":
      case "value":
      case "autoReverse":
      case "externalResourcesRequired":
      case "focusable":
      case "preserveAlpha":
        r != null && typeof r != "function" && typeof r != "symbol" ? e.setAttribute(n, "" + r) : e.removeAttribute(n);
        break;
      case "inert":
      case "allowFullScreen":
      case "async":
      case "autoPlay":
      case "controls":
      case "default":
      case "defer":
      case "disabled":
      case "disablePictureInPicture":
      case "disableRemotePlayback":
      case "formNoValidate":
      case "hidden":
      case "loop":
      case "noModule":
      case "noValidate":
      case "open":
      case "playsInline":
      case "readOnly":
      case "required":
      case "reversed":
      case "scoped":
      case "seamless":
      case "itemScope":
        r && typeof r != "function" && typeof r != "symbol" ? e.setAttribute(n, "") : e.removeAttribute(n);
        break;
      case "capture":
      case "download":
        r === true ? e.setAttribute(n, "") : r !== false && r != null && typeof r != "function" && typeof r != "symbol" ? e.setAttribute(n, r) : e.removeAttribute(n);
        break;
      case "cols":
      case "rows":
      case "size":
      case "span":
        r != null && typeof r != "function" && typeof r != "symbol" && !isNaN(r) && 1 <= r ? e.setAttribute(n, r) : e.removeAttribute(n);
        break;
      case "rowSpan":
      case "start":
        r == null || typeof r == "function" || typeof r == "symbol" || isNaN(r) ? e.removeAttribute(n) : e.setAttribute(n, r);
        break;
      case "popover":
        re("beforetoggle", e), re("toggle", e), Na(e, "popover", r);
        break;
      case "xlinkActuate":
        zt(e, "http://www.w3.org/1999/xlink", "xlink:actuate", r);
        break;
      case "xlinkArcrole":
        zt(e, "http://www.w3.org/1999/xlink", "xlink:arcrole", r);
        break;
      case "xlinkRole":
        zt(e, "http://www.w3.org/1999/xlink", "xlink:role", r);
        break;
      case "xlinkShow":
        zt(e, "http://www.w3.org/1999/xlink", "xlink:show", r);
        break;
      case "xlinkTitle":
        zt(e, "http://www.w3.org/1999/xlink", "xlink:title", r);
        break;
      case "xlinkType":
        zt(e, "http://www.w3.org/1999/xlink", "xlink:type", r);
        break;
      case "xmlBase":
        zt(e, "http://www.w3.org/XML/1998/namespace", "xml:base", r);
        break;
      case "xmlLang":
        zt(e, "http://www.w3.org/XML/1998/namespace", "xml:lang", r);
        break;
      case "xmlSpace":
        zt(e, "http://www.w3.org/XML/1998/namespace", "xml:space", r);
        break;
      case "is":
        Na(e, "is", r);
        break;
      case "innerText":
      case "textContent":
        break;
      default:
        (!(2 < n.length) || n[0] !== "o" && n[0] !== "O" || n[1] !== "n" && n[1] !== "N") && (n = jf.get(n) || n, Na(e, n, r));
    }
  }
  function Ao(e, t, n, r, a, l) {
    switch (n) {
      case "style":
        xs(e, r, l);
        break;
      case "dangerouslySetInnerHTML":
        if (r != null) {
          if (typeof r != "object" || !("__html" in r)) throw Error(o(61));
          if (n = r.__html, n != null) {
            if (a.children != null) throw Error(o(60));
            e.innerHTML = n;
          }
        }
        break;
      case "children":
        typeof r == "string" ? Wn(e, r) : (typeof r == "number" || typeof r == "bigint") && Wn(e, "" + r);
        break;
      case "onScroll":
        r != null && re("scroll", e);
        break;
      case "onScrollEnd":
        r != null && re("scrollend", e);
        break;
      case "onClick":
        r != null && (e.onclick = xl);
        break;
      case "suppressContentEditableWarning":
      case "suppressHydrationWarning":
      case "innerHTML":
      case "ref":
        break;
      case "innerText":
      case "textContent":
        break;
      default:
        if (!us.hasOwnProperty(n)) e: {
          if (n[0] === "o" && n[1] === "n" && (a = n.endsWith("Capture"), t = n.slice(2, a ? n.length - 7 : void 0), l = e[Ge] || null, l = l != null ? l[n] : null, typeof l == "function" && e.removeEventListener(t, l, a), typeof r == "function")) {
            typeof l != "function" && l !== null && (n in e ? e[n] = null : e.hasAttribute(n) && e.removeAttribute(n)), e.addEventListener(t, r, a);
            break e;
          }
          n in e ? e[n] = r : r === true ? e.setAttribute(n, "") : Na(e, n, r);
        }
    }
  }
  function qe(e, t, n) {
    switch (t) {
      case "div":
      case "span":
      case "svg":
      case "path":
      case "a":
      case "g":
      case "p":
      case "li":
        break;
      case "img":
        re("error", e), re("load", e);
        var r = false, a = false, l;
        for (l in n) if (n.hasOwnProperty(l)) {
          var i = n[l];
          if (i != null) switch (l) {
            case "src":
              r = true;
              break;
            case "srcSet":
              a = true;
              break;
            case "children":
            case "dangerouslySetInnerHTML":
              throw Error(o(137, t));
            default:
              ye(e, t, l, i, n, null);
          }
        }
        a && ye(e, t, "srcSet", n.srcSet, n, null), r && ye(e, t, "src", n.src, n, null);
        return;
      case "input":
        re("invalid", e);
        var u = l = i = a = null, d = null, y = null;
        for (r in n) if (n.hasOwnProperty(r)) {
          var w = n[r];
          if (w != null) switch (r) {
            case "name":
              a = w;
              break;
            case "type":
              i = w;
              break;
            case "checked":
              d = w;
              break;
            case "defaultChecked":
              y = w;
              break;
            case "value":
              l = w;
              break;
            case "defaultValue":
              u = w;
              break;
            case "children":
            case "dangerouslySetInnerHTML":
              if (w != null) throw Error(o(137, t));
              break;
            default:
              ye(e, t, r, w, n, null);
          }
        }
        gs(e, l, u, d, y, i, a, false), _a(e);
        return;
      case "select":
        re("invalid", e), r = i = l = null;
        for (a in n) if (n.hasOwnProperty(a) && (u = n[a], u != null)) switch (a) {
          case "value":
            l = u;
            break;
          case "defaultValue":
            i = u;
            break;
          case "multiple":
            r = u;
          default:
            ye(e, t, a, u, n, null);
        }
        t = l, n = i, e.multiple = !!r, t != null ? Bn(e, !!r, t, false) : n != null && Bn(e, !!r, n, true);
        return;
      case "textarea":
        re("invalid", e), l = a = r = null;
        for (i in n) if (n.hasOwnProperty(i) && (u = n[i], u != null)) switch (i) {
          case "value":
            r = u;
            break;
          case "defaultValue":
            a = u;
            break;
          case "children":
            l = u;
            break;
          case "dangerouslySetInnerHTML":
            if (u != null) throw Error(o(91));
            break;
          default:
            ye(e, t, i, u, n, null);
        }
        vs(e, r, a, l), _a(e);
        return;
      case "option":
        for (d in n) if (n.hasOwnProperty(d) && (r = n[d], r != null)) switch (d) {
          case "selected":
            e.selected = r && typeof r != "function" && typeof r != "symbol";
            break;
          default:
            ye(e, t, d, r, n, null);
        }
        return;
      case "dialog":
        re("beforetoggle", e), re("toggle", e), re("cancel", e), re("close", e);
        break;
      case "iframe":
      case "object":
        re("load", e);
        break;
      case "video":
      case "audio":
        for (r = 0; r < ia.length; r++) re(ia[r], e);
        break;
      case "image":
        re("error", e), re("load", e);
        break;
      case "details":
        re("toggle", e);
        break;
      case "embed":
      case "source":
      case "link":
        re("error", e), re("load", e);
      case "area":
      case "base":
      case "br":
      case "col":
      case "hr":
      case "keygen":
      case "meta":
      case "param":
      case "track":
      case "wbr":
      case "menuitem":
        for (y in n) if (n.hasOwnProperty(y) && (r = n[y], r != null)) switch (y) {
          case "children":
          case "dangerouslySetInnerHTML":
            throw Error(o(137, t));
          default:
            ye(e, t, y, r, n, null);
        }
        return;
      default:
        if (Ql(t)) {
          for (w in n) n.hasOwnProperty(w) && (r = n[w], r !== void 0 && Ao(e, t, w, r, n, void 0));
          return;
        }
    }
    for (u in n) n.hasOwnProperty(u) && (r = n[u], r != null && ye(e, t, u, r, n, null));
  }
  function Zp(e, t, n, r) {
    switch (t) {
      case "div":
      case "span":
      case "svg":
      case "path":
      case "a":
      case "g":
      case "p":
      case "li":
        break;
      case "input":
        var a = null, l = null, i = null, u = null, d = null, y = null, w = null;
        for (b in n) {
          var E = n[b];
          if (n.hasOwnProperty(b) && E != null) switch (b) {
            case "checked":
              break;
            case "value":
              break;
            case "defaultValue":
              d = E;
            default:
              r.hasOwnProperty(b) || ye(e, t, b, null, r, E);
          }
        }
        for (var v in r) {
          var b = r[v];
          if (E = n[v], r.hasOwnProperty(v) && (b != null || E != null)) switch (v) {
            case "type":
              l = b;
              break;
            case "name":
              a = b;
              break;
            case "checked":
              y = b;
              break;
            case "defaultChecked":
              w = b;
              break;
            case "value":
              i = b;
              break;
            case "defaultValue":
              u = b;
              break;
            case "children":
            case "dangerouslySetInnerHTML":
              if (b != null) throw Error(o(137, t));
              break;
            default:
              b !== E && ye(e, t, v, b, r, E);
          }
        }
        Kl(e, i, u, d, y, w, l, a);
        return;
      case "select":
        b = i = u = v = null;
        for (l in n) if (d = n[l], n.hasOwnProperty(l) && d != null) switch (l) {
          case "value":
            break;
          case "multiple":
            b = d;
          default:
            r.hasOwnProperty(l) || ye(e, t, l, null, r, d);
        }
        for (a in r) if (l = r[a], d = n[a], r.hasOwnProperty(a) && (l != null || d != null)) switch (a) {
          case "value":
            v = l;
            break;
          case "defaultValue":
            u = l;
            break;
          case "multiple":
            i = l;
          default:
            l !== d && ye(e, t, a, l, r, d);
        }
        t = u, n = i, r = b, v != null ? Bn(e, !!n, v, false) : !!r != !!n && (t != null ? Bn(e, !!n, t, true) : Bn(e, !!n, n ? [] : "", false));
        return;
      case "textarea":
        b = v = null;
        for (u in n) if (a = n[u], n.hasOwnProperty(u) && a != null && !r.hasOwnProperty(u)) switch (u) {
          case "value":
            break;
          case "children":
            break;
          default:
            ye(e, t, u, null, r, a);
        }
        for (i in r) if (a = r[i], l = n[i], r.hasOwnProperty(i) && (a != null || l != null)) switch (i) {
          case "value":
            v = a;
            break;
          case "defaultValue":
            b = a;
            break;
          case "children":
            break;
          case "dangerouslySetInnerHTML":
            if (a != null) throw Error(o(91));
            break;
          default:
            a !== l && ye(e, t, i, a, r, l);
        }
        ys(e, v, b);
        return;
      case "option":
        for (var Q in n) if (v = n[Q], n.hasOwnProperty(Q) && v != null && !r.hasOwnProperty(Q)) switch (Q) {
          case "selected":
            e.selected = false;
            break;
          default:
            ye(e, t, Q, null, r, v);
        }
        for (d in r) if (v = r[d], b = n[d], r.hasOwnProperty(d) && v !== b && (v != null || b != null)) switch (d) {
          case "selected":
            e.selected = v && typeof v != "function" && typeof v != "symbol";
            break;
          default:
            ye(e, t, d, v, r, b);
        }
        return;
      case "img":
      case "link":
      case "area":
      case "base":
      case "br":
      case "col":
      case "embed":
      case "hr":
      case "keygen":
      case "meta":
      case "param":
      case "source":
      case "track":
      case "wbr":
      case "menuitem":
        for (var H in n) v = n[H], n.hasOwnProperty(H) && v != null && !r.hasOwnProperty(H) && ye(e, t, H, null, r, v);
        for (y in r) if (v = r[y], b = n[y], r.hasOwnProperty(y) && v !== b && (v != null || b != null)) switch (y) {
          case "children":
          case "dangerouslySetInnerHTML":
            if (v != null) throw Error(o(137, t));
            break;
          default:
            ye(e, t, y, v, r, b);
        }
        return;
      default:
        if (Ql(t)) {
          for (var ve in n) v = n[ve], n.hasOwnProperty(ve) && v !== void 0 && !r.hasOwnProperty(ve) && Ao(e, t, ve, void 0, r, v);
          for (w in r) v = r[w], b = n[w], !r.hasOwnProperty(w) || v === b || v === void 0 && b === void 0 || Ao(e, t, w, v, r, b);
          return;
        }
    }
    for (var h in n) v = n[h], n.hasOwnProperty(h) && v != null && !r.hasOwnProperty(h) && ye(e, t, h, null, r, v);
    for (E in r) v = r[E], b = n[E], !r.hasOwnProperty(E) || v === b || v == null && b == null || ye(e, t, E, v, r, b);
  }
  var Ro = null, Oo = null;
  function wl(e) {
    return e.nodeType === 9 ? e : e.ownerDocument;
  }
  function bd(e) {
    switch (e) {
      case "http://www.w3.org/2000/svg":
        return 1;
      case "http://www.w3.org/1998/Math/MathML":
        return 2;
      default:
        return 0;
    }
  }
  function xd(e, t) {
    if (e === 0) switch (t) {
      case "svg":
        return 1;
      case "math":
        return 2;
      default:
        return 0;
    }
    return e === 1 && t === "foreignObject" ? 0 : e;
  }
  function Fo(e, t) {
    return e === "textarea" || e === "noscript" || typeof t.children == "string" || typeof t.children == "number" || typeof t.children == "bigint" || typeof t.dangerouslySetInnerHTML == "object" && t.dangerouslySetInnerHTML !== null && t.dangerouslySetInnerHTML.__html != null;
  }
  var Do = null;
  function Jp() {
    var e = window.event;
    return e && e.type === "popstate" ? e === Do ? false : (Do = e, true) : (Do = null, false);
  }
  var wd = typeof setTimeout == "function" ? setTimeout : void 0, eh = typeof clearTimeout == "function" ? clearTimeout : void 0, kd = typeof Promise == "function" ? Promise : void 0, th = typeof queueMicrotask == "function" ? queueMicrotask : typeof kd < "u" ? function(e) {
    return kd.resolve(null).then(e).catch(nh);
  } : wd;
  function nh(e) {
    setTimeout(function() {
      throw e;
    });
  }
  function dn(e) {
    return e === "head";
  }
  function Sd(e, t) {
    var n = t, r = 0, a = 0;
    do {
      var l = n.nextSibling;
      if (e.removeChild(n), l && l.nodeType === 8) if (n = l.data, n === "/$") {
        if (0 < r && 8 > r) {
          n = r;
          var i = e.ownerDocument;
          if (n & 1 && sa(i.documentElement), n & 2 && sa(i.body), n & 4) for (n = i.head, sa(n), i = n.firstChild; i; ) {
            var u = i.nextSibling, d = i.nodeName;
            i[Sr] || d === "SCRIPT" || d === "STYLE" || d === "LINK" && i.rel.toLowerCase() === "stylesheet" || n.removeChild(i), i = u;
          }
        }
        if (a === 0) {
          e.removeChild(l), ga(t);
          return;
        }
        a--;
      } else n === "$" || n === "$?" || n === "$!" ? a++ : r = n.charCodeAt(0) - 48;
      else r = 0;
      n = l;
    } while (n);
    ga(t);
  }
  function Mo(e) {
    var t = e.firstChild;
    for (t && t.nodeType === 10 && (t = t.nextSibling); t; ) {
      var n = t;
      switch (t = t.nextSibling, n.nodeName) {
        case "HTML":
        case "HEAD":
        case "BODY":
          Mo(n), Wl(n);
          continue;
        case "SCRIPT":
        case "STYLE":
          continue;
        case "LINK":
          if (n.rel.toLowerCase() === "stylesheet") continue;
      }
      e.removeChild(n);
    }
  }
  function rh(e, t, n, r) {
    for (; e.nodeType === 1; ) {
      var a = n;
      if (e.nodeName.toLowerCase() !== t.toLowerCase()) {
        if (!r && (e.nodeName !== "INPUT" || e.type !== "hidden")) break;
      } else if (r) {
        if (!e[Sr]) switch (t) {
          case "meta":
            if (!e.hasAttribute("itemprop")) break;
            return e;
          case "link":
            if (l = e.getAttribute("rel"), l === "stylesheet" && e.hasAttribute("data-precedence") || l !== a.rel || e.getAttribute("href") !== (a.href == null || a.href === "" ? null : a.href) || e.getAttribute("crossorigin") !== (a.crossOrigin == null ? null : a.crossOrigin) || e.getAttribute("title") !== (a.title == null ? null : a.title)) break;
            return e;
          case "style":
            if (e.hasAttribute("data-precedence")) break;
            return e;
          case "script":
            if (l = e.getAttribute("src"), (l !== (a.src == null ? null : a.src) || e.getAttribute("type") !== (a.type == null ? null : a.type) || e.getAttribute("crossorigin") !== (a.crossOrigin == null ? null : a.crossOrigin)) && l && e.hasAttribute("async") && !e.hasAttribute("itemprop")) break;
            return e;
          default:
            return e;
        }
      } else if (t === "input" && e.type === "hidden") {
        var l = a.name == null ? null : "" + a.name;
        if (a.type === "hidden" && e.getAttribute("name") === l) return e;
      } else return e;
      if (e = kt(e.nextSibling), e === null) break;
    }
    return null;
  }
  function ah(e, t, n) {
    if (t === "") return null;
    for (; e.nodeType !== 3; ) if ((e.nodeType !== 1 || e.nodeName !== "INPUT" || e.type !== "hidden") && !n || (e = kt(e.nextSibling), e === null)) return null;
    return e;
  }
  function Io(e) {
    return e.data === "$!" || e.data === "$?" && e.ownerDocument.readyState === "complete";
  }
  function lh(e, t) {
    var n = e.ownerDocument;
    if (e.data !== "$?" || n.readyState === "complete") t();
    else {
      var r = function() {
        t(), n.removeEventListener("DOMContentLoaded", r);
      };
      n.addEventListener("DOMContentLoaded", r), e._reactRetry = r;
    }
  }
  function kt(e) {
    for (; e != null; e = e.nextSibling) {
      var t = e.nodeType;
      if (t === 1 || t === 3) break;
      if (t === 8) {
        if (t = e.data, t === "$" || t === "$!" || t === "$?" || t === "F!" || t === "F") break;
        if (t === "/$") return null;
      }
    }
    return e;
  }
  var Uo = null;
  function Nd(e) {
    e = e.previousSibling;
    for (var t = 0; e; ) {
      if (e.nodeType === 8) {
        var n = e.data;
        if (n === "$" || n === "$!" || n === "$?") {
          if (t === 0) return e;
          t--;
        } else n === "/$" && t++;
      }
      e = e.previousSibling;
    }
    return null;
  }
  function Ed(e, t, n) {
    switch (t = wl(n), e) {
      case "html":
        if (e = t.documentElement, !e) throw Error(o(452));
        return e;
      case "head":
        if (e = t.head, !e) throw Error(o(453));
        return e;
      case "body":
        if (e = t.body, !e) throw Error(o(454));
        return e;
      default:
        throw Error(o(451));
    }
  }
  function sa(e) {
    for (var t = e.attributes; t.length; ) e.removeAttributeNode(t[0]);
    Wl(e);
  }
  var vt = /* @__PURE__ */ new Map(), _d = /* @__PURE__ */ new Set();
  function kl(e) {
    return typeof e.getRootNode == "function" ? e.getRootNode() : e.nodeType === 9 ? e : e.ownerDocument;
  }
  var $t = z.d;
  z.d = { f: ih, r: oh, D: sh, C: uh, L: ch, m: dh, X: ph, S: fh, M: hh };
  function ih() {
    var e = $t.f(), t = pl();
    return e || t;
  }
  function oh(e) {
    var t = Dn(e);
    t !== null && t.tag === 5 && t.type === "form" ? Ku(t) : $t.r(e);
  }
  var yr = typeof document > "u" ? null : document;
  function jd(e, t, n) {
    var r = yr;
    if (r && typeof t == "string" && t) {
      var a = bt(t);
      a = 'link[rel="' + e + '"][href="' + a + '"]', typeof n == "string" && (a += '[crossorigin="' + n + '"]'), _d.has(a) || (_d.add(a), e = { rel: e, crossOrigin: n, href: t }, r.querySelector(a) === null && (t = r.createElement("link"), qe(t, "link", e), Ie(t), r.head.appendChild(t)));
    }
  }
  function sh(e) {
    $t.D(e), jd("dns-prefetch", e, null);
  }
  function uh(e, t) {
    $t.C(e, t), jd("preconnect", e, t);
  }
  function ch(e, t, n) {
    $t.L(e, t, n);
    var r = yr;
    if (r && e && t) {
      var a = 'link[rel="preload"][as="' + bt(t) + '"]';
      t === "image" && n && n.imageSrcSet ? (a += '[imagesrcset="' + bt(n.imageSrcSet) + '"]', typeof n.imageSizes == "string" && (a += '[imagesizes="' + bt(n.imageSizes) + '"]')) : a += '[href="' + bt(e) + '"]';
      var l = a;
      switch (t) {
        case "style":
          l = vr(e);
          break;
        case "script":
          l = br(e);
      }
      vt.has(l) || (e = R({ rel: "preload", href: t === "image" && n && n.imageSrcSet ? void 0 : e, as: t }, n), vt.set(l, e), r.querySelector(a) !== null || t === "style" && r.querySelector(ua(l)) || t === "script" && r.querySelector(ca(l)) || (t = r.createElement("link"), qe(t, "link", e), Ie(t), r.head.appendChild(t)));
    }
  }
  function dh(e, t) {
    $t.m(e, t);
    var n = yr;
    if (n && e) {
      var r = t && typeof t.as == "string" ? t.as : "script", a = 'link[rel="modulepreload"][as="' + bt(r) + '"][href="' + bt(e) + '"]', l = a;
      switch (r) {
        case "audioworklet":
        case "paintworklet":
        case "serviceworker":
        case "sharedworker":
        case "worker":
        case "script":
          l = br(e);
      }
      if (!vt.has(l) && (e = R({ rel: "modulepreload", href: e }, t), vt.set(l, e), n.querySelector(a) === null)) {
        switch (r) {
          case "audioworklet":
          case "paintworklet":
          case "serviceworker":
          case "sharedworker":
          case "worker":
          case "script":
            if (n.querySelector(ca(l))) return;
        }
        r = n.createElement("link"), qe(r, "link", e), Ie(r), n.head.appendChild(r);
      }
    }
  }
  function fh(e, t, n) {
    $t.S(e, t, n);
    var r = yr;
    if (r && e) {
      var a = Mn(r).hoistableStyles, l = vr(e);
      t = t || "default";
      var i = a.get(l);
      if (!i) {
        var u = { loading: 0, preload: null };
        if (i = r.querySelector(ua(l))) u.loading = 5;
        else {
          e = R({ rel: "stylesheet", href: e, "data-precedence": t }, n), (n = vt.get(l)) && Bo(e, n);
          var d = i = r.createElement("link");
          Ie(d), qe(d, "link", e), d._p = new Promise(function(y, w) {
            d.onload = y, d.onerror = w;
          }), d.addEventListener("load", function() {
            u.loading |= 1;
          }), d.addEventListener("error", function() {
            u.loading |= 2;
          }), u.loading |= 4, Sl(i, t, r);
        }
        i = { type: "stylesheet", instance: i, count: 1, state: u }, a.set(l, i);
      }
    }
  }
  function ph(e, t) {
    $t.X(e, t);
    var n = yr;
    if (n && e) {
      var r = Mn(n).hoistableScripts, a = br(e), l = r.get(a);
      l || (l = n.querySelector(ca(a)), l || (e = R({ src: e, async: true }, t), (t = vt.get(a)) && Wo(e, t), l = n.createElement("script"), Ie(l), qe(l, "link", e), n.head.appendChild(l)), l = { type: "script", instance: l, count: 1, state: null }, r.set(a, l));
    }
  }
  function hh(e, t) {
    $t.M(e, t);
    var n = yr;
    if (n && e) {
      var r = Mn(n).hoistableScripts, a = br(e), l = r.get(a);
      l || (l = n.querySelector(ca(a)), l || (e = R({ src: e, async: true, type: "module" }, t), (t = vt.get(a)) && Wo(e, t), l = n.createElement("script"), Ie(l), qe(l, "link", e), n.head.appendChild(l)), l = { type: "script", instance: l, count: 1, state: null }, r.set(a, l));
    }
  }
  function Cd(e, t, n, r) {
    var a = (a = Y.current) ? kl(a) : null;
    if (!a) throw Error(o(446));
    switch (e) {
      case "meta":
      case "title":
        return null;
      case "style":
        return typeof n.precedence == "string" && typeof n.href == "string" ? (t = vr(n.href), n = Mn(a).hoistableStyles, r = n.get(t), r || (r = { type: "style", instance: null, count: 0, state: null }, n.set(t, r)), r) : { type: "void", instance: null, count: 0, state: null };
      case "link":
        if (n.rel === "stylesheet" && typeof n.href == "string" && typeof n.precedence == "string") {
          e = vr(n.href);
          var l = Mn(a).hoistableStyles, i = l.get(e);
          if (i || (a = a.ownerDocument || a, i = { type: "stylesheet", instance: null, count: 0, state: { loading: 0, preload: null } }, l.set(e, i), (l = a.querySelector(ua(e))) && !l._p && (i.instance = l, i.state.loading = 5), vt.has(e) || (n = { rel: "preload", as: "style", href: n.href, crossOrigin: n.crossOrigin, integrity: n.integrity, media: n.media, hrefLang: n.hrefLang, referrerPolicy: n.referrerPolicy }, vt.set(e, n), l || mh(a, e, n, i.state))), t && r === null) throw Error(o(528, ""));
          return i;
        }
        if (t && r !== null) throw Error(o(529, ""));
        return null;
      case "script":
        return t = n.async, n = n.src, typeof n == "string" && t && typeof t != "function" && typeof t != "symbol" ? (t = br(n), n = Mn(a).hoistableScripts, r = n.get(t), r || (r = { type: "script", instance: null, count: 0, state: null }, n.set(t, r)), r) : { type: "void", instance: null, count: 0, state: null };
      default:
        throw Error(o(444, e));
    }
  }
  function vr(e) {
    return 'href="' + bt(e) + '"';
  }
  function ua(e) {
    return 'link[rel="stylesheet"][' + e + "]";
  }
  function zd(e) {
    return R({}, e, { "data-precedence": e.precedence, precedence: null });
  }
  function mh(e, t, n, r) {
    e.querySelector('link[rel="preload"][as="style"][' + t + "]") ? r.loading = 1 : (t = e.createElement("link"), r.preload = t, t.addEventListener("load", function() {
      return r.loading |= 1;
    }), t.addEventListener("error", function() {
      return r.loading |= 2;
    }), qe(t, "link", n), Ie(t), e.head.appendChild(t));
  }
  function br(e) {
    return '[src="' + bt(e) + '"]';
  }
  function ca(e) {
    return "script[async]" + e;
  }
  function Pd(e, t, n) {
    if (t.count++, t.instance === null) switch (t.type) {
      case "style":
        var r = e.querySelector('style[data-href~="' + bt(n.href) + '"]');
        if (r) return t.instance = r, Ie(r), r;
        var a = R({}, n, { "data-href": n.href, "data-precedence": n.precedence, href: null, precedence: null });
        return r = (e.ownerDocument || e).createElement("style"), Ie(r), qe(r, "style", a), Sl(r, n.precedence, e), t.instance = r;
      case "stylesheet":
        a = vr(n.href);
        var l = e.querySelector(ua(a));
        if (l) return t.state.loading |= 4, t.instance = l, Ie(l), l;
        r = zd(n), (a = vt.get(a)) && Bo(r, a), l = (e.ownerDocument || e).createElement("link"), Ie(l);
        var i = l;
        return i._p = new Promise(function(u, d) {
          i.onload = u, i.onerror = d;
        }), qe(l, "link", r), t.state.loading |= 4, Sl(l, n.precedence, e), t.instance = l;
      case "script":
        return l = br(n.src), (a = e.querySelector(ca(l))) ? (t.instance = a, Ie(a), a) : (r = n, (a = vt.get(l)) && (r = R({}, n), Wo(r, a)), e = e.ownerDocument || e, a = e.createElement("script"), Ie(a), qe(a, "link", r), e.head.appendChild(a), t.instance = a);
      case "void":
        return null;
      default:
        throw Error(o(443, t.type));
    }
    else t.type === "stylesheet" && (t.state.loading & 4) === 0 && (r = t.instance, t.state.loading |= 4, Sl(r, n.precedence, e));
    return t.instance;
  }
  function Sl(e, t, n) {
    for (var r = n.querySelectorAll('link[rel="stylesheet"][data-precedence],style[data-precedence]'), a = r.length ? r[r.length - 1] : null, l = a, i = 0; i < r.length; i++) {
      var u = r[i];
      if (u.dataset.precedence === t) l = u;
      else if (l !== a) break;
    }
    l ? l.parentNode.insertBefore(e, l.nextSibling) : (t = n.nodeType === 9 ? n.head : n, t.insertBefore(e, t.firstChild));
  }
  function Bo(e, t) {
    e.crossOrigin == null && (e.crossOrigin = t.crossOrigin), e.referrerPolicy == null && (e.referrerPolicy = t.referrerPolicy), e.title == null && (e.title = t.title);
  }
  function Wo(e, t) {
    e.crossOrigin == null && (e.crossOrigin = t.crossOrigin), e.referrerPolicy == null && (e.referrerPolicy = t.referrerPolicy), e.integrity == null && (e.integrity = t.integrity);
  }
  var Nl = null;
  function Ld(e, t, n) {
    if (Nl === null) {
      var r = /* @__PURE__ */ new Map(), a = Nl = /* @__PURE__ */ new Map();
      a.set(n, r);
    } else a = Nl, r = a.get(n), r || (r = /* @__PURE__ */ new Map(), a.set(n, r));
    if (r.has(e)) return r;
    for (r.set(e, null), n = n.getElementsByTagName(e), a = 0; a < n.length; a++) {
      var l = n[a];
      if (!(l[Sr] || l[Ke] || e === "link" && l.getAttribute("rel") === "stylesheet") && l.namespaceURI !== "http://www.w3.org/2000/svg") {
        var i = l.getAttribute(t) || "";
        i = e + i;
        var u = r.get(i);
        u ? u.push(l) : r.set(i, [l]);
      }
    }
    return r;
  }
  function Td(e, t, n) {
    e = e.ownerDocument || e, e.head.insertBefore(n, t === "title" ? e.querySelector("head > title") : null);
  }
  function gh(e, t, n) {
    if (n === 1 || t.itemProp != null) return false;
    switch (e) {
      case "meta":
      case "title":
        return true;
      case "style":
        if (typeof t.precedence != "string" || typeof t.href != "string" || t.href === "") break;
        return true;
      case "link":
        if (typeof t.rel != "string" || typeof t.href != "string" || t.href === "" || t.onLoad || t.onError) break;
        switch (t.rel) {
          case "stylesheet":
            return e = t.disabled, typeof t.precedence == "string" && e == null;
          default:
            return true;
        }
      case "script":
        if (t.async && typeof t.async != "function" && typeof t.async != "symbol" && !t.onLoad && !t.onError && t.src && typeof t.src == "string") return true;
    }
    return false;
  }
  function Ad(e) {
    return !(e.type === "stylesheet" && (e.state.loading & 3) === 0);
  }
  var da = null;
  function yh() {
  }
  function vh(e, t, n) {
    if (da === null) throw Error(o(475));
    var r = da;
    if (t.type === "stylesheet" && (typeof n.media != "string" || matchMedia(n.media).matches !== false) && (t.state.loading & 4) === 0) {
      if (t.instance === null) {
        var a = vr(n.href), l = e.querySelector(ua(a));
        if (l) {
          e = l._p, e !== null && typeof e == "object" && typeof e.then == "function" && (r.count++, r = El.bind(r), e.then(r, r)), t.state.loading |= 4, t.instance = l, Ie(l);
          return;
        }
        l = e.ownerDocument || e, n = zd(n), (a = vt.get(a)) && Bo(n, a), l = l.createElement("link"), Ie(l);
        var i = l;
        i._p = new Promise(function(u, d) {
          i.onload = u, i.onerror = d;
        }), qe(l, "link", n), t.instance = l;
      }
      r.stylesheets === null && (r.stylesheets = /* @__PURE__ */ new Map()), r.stylesheets.set(t, e), (e = t.state.preload) && (t.state.loading & 3) === 0 && (r.count++, t = El.bind(r), e.addEventListener("load", t), e.addEventListener("error", t));
    }
  }
  function bh() {
    if (da === null) throw Error(o(475));
    var e = da;
    return e.stylesheets && e.count === 0 && Ho(e, e.stylesheets), 0 < e.count ? function(t) {
      var n = setTimeout(function() {
        if (e.stylesheets && Ho(e, e.stylesheets), e.unsuspend) {
          var r = e.unsuspend;
          e.unsuspend = null, r();
        }
      }, 6e4);
      return e.unsuspend = t, function() {
        e.unsuspend = null, clearTimeout(n);
      };
    } : null;
  }
  function El() {
    if (this.count--, this.count === 0) {
      if (this.stylesheets) Ho(this, this.stylesheets);
      else if (this.unsuspend) {
        var e = this.unsuspend;
        this.unsuspend = null, e();
      }
    }
  }
  var _l = null;
  function Ho(e, t) {
    e.stylesheets = null, e.unsuspend !== null && (e.count++, _l = /* @__PURE__ */ new Map(), t.forEach(xh, e), _l = null, El.call(e));
  }
  function xh(e, t) {
    if (!(t.state.loading & 4)) {
      var n = _l.get(e);
      if (n) var r = n.get(null);
      else {
        n = /* @__PURE__ */ new Map(), _l.set(e, n);
        for (var a = e.querySelectorAll("link[data-precedence],style[data-precedence]"), l = 0; l < a.length; l++) {
          var i = a[l];
          (i.nodeName === "LINK" || i.getAttribute("media") !== "not all") && (n.set(i.dataset.precedence, i), r = i);
        }
        r && n.set(null, r);
      }
      a = t.instance, i = a.getAttribute("data-precedence"), l = n.get(i) || r, l === r && n.set(null, a), n.set(i, a), this.count++, r = El.bind(this), a.addEventListener("load", r), a.addEventListener("error", r), l ? l.parentNode.insertBefore(a, l.nextSibling) : (e = e.nodeType === 9 ? e.head : e, e.insertBefore(a, e.firstChild)), t.state.loading |= 4;
    }
  }
  var fa = { $$typeof: Ee, Provider: null, Consumer: null, _currentValue: q, _currentValue2: q, _threadCount: 0 };
  function wh(e, t, n, r, a, l, i, u) {
    this.tag = 1, this.containerInfo = e, this.pingCache = this.current = this.pendingChildren = null, this.timeoutHandle = -1, this.callbackNode = this.next = this.pendingContext = this.context = this.cancelPendingCommit = null, this.callbackPriority = 0, this.expirationTimes = Ml(-1), this.entangledLanes = this.shellSuspendCounter = this.errorRecoveryDisabledLanes = this.expiredLanes = this.warmLanes = this.pingedLanes = this.suspendedLanes = this.pendingLanes = 0, this.entanglements = Ml(0), this.hiddenUpdates = Ml(null), this.identifierPrefix = r, this.onUncaughtError = a, this.onCaughtError = l, this.onRecoverableError = i, this.pooledCache = null, this.pooledCacheLanes = 0, this.formState = u, this.incompleteTransitions = /* @__PURE__ */ new Map();
  }
  function Rd(e, t, n, r, a, l, i, u, d, y, w, E) {
    return e = new wh(e, t, n, i, u, d, y, E), t = 1, l === true && (t |= 24), l = lt(3, null, null, t), e.current = l, l.stateNode = e, t = Si(), t.refCount++, e.pooledCache = t, t.refCount++, l.memoizedState = { element: r, isDehydrated: n, cache: t }, ji(l), e;
  }
  function Od(e) {
    return e ? (e = Gn, e) : Gn;
  }
  function Fd(e, t, n, r, a, l) {
    a = Od(a), r.context === null ? r.context = a : r.pendingContext = a, r = Xt(t), r.payload = { element: n }, l = l === void 0 ? null : l, l !== null && (r.callback = l), n = Zt(e, r, t), n !== null && (ct(n, e, t), Wr(n, e, t));
  }
  function Dd(e, t) {
    if (e = e.memoizedState, e !== null && e.dehydrated !== null) {
      var n = e.retryLane;
      e.retryLane = n !== 0 && n < t ? n : t;
    }
  }
  function $o(e, t) {
    Dd(e, t), (e = e.alternate) && Dd(e, t);
  }
  function Md(e) {
    if (e.tag === 13) {
      var t = Yn(e, 67108864);
      t !== null && ct(t, e, 67108864), $o(e, 67108864);
    }
  }
  var jl = true;
  function kh(e, t, n, r) {
    var a = x.T;
    x.T = null;
    var l = z.p;
    try {
      z.p = 2, qo(e, t, n, r);
    } finally {
      z.p = l, x.T = a;
    }
  }
  function Sh(e, t, n, r) {
    var a = x.T;
    x.T = null;
    var l = z.p;
    try {
      z.p = 8, qo(e, t, n, r);
    } finally {
      z.p = l, x.T = a;
    }
  }
  function qo(e, t, n, r) {
    if (jl) {
      var a = Ko(r);
      if (a === null) To(e, t, r, Cl, n), Ud(e, r);
      else if (Eh(a, e, t, n, r)) r.stopPropagation();
      else if (Ud(e, r), t & 4 && -1 < Nh.indexOf(e)) {
        for (; a !== null; ) {
          var l = Dn(a);
          if (l !== null) switch (l.tag) {
            case 3:
              if (l = l.stateNode, l.current.memoizedState.isDehydrated) {
                var i = yn(l.pendingLanes);
                if (i !== 0) {
                  var u = l;
                  for (u.pendingLanes |= 2, u.entangledLanes |= 2; i; ) {
                    var d = 1 << 31 - rt(i);
                    u.entanglements[1] |= d, i &= ~d;
                  }
                  jt(l), (he & 6) === 0 && (dl = St() + 500, la(0));
                }
              }
              break;
            case 13:
              u = Yn(l, 2), u !== null && ct(u, l, 2), pl(), $o(l, 2);
          }
          if (l = Ko(r), l === null && To(e, t, r, Cl, n), l === a) break;
          a = l;
        }
        a !== null && r.stopPropagation();
      } else To(e, t, r, null, n);
    }
  }
  function Ko(e) {
    return e = Gl(e), Vo(e);
  }
  var Cl = null;
  function Vo(e) {
    if (Cl = null, e = Fn(e), e !== null) {
      var t = k(e);
      if (t === null) e = null;
      else {
        var n = t.tag;
        if (n === 13) {
          if (e = se(t), e !== null) return e;
          e = null;
        } else if (n === 3) {
          if (t.stateNode.current.memoizedState.isDehydrated) return t.tag === 3 ? t.stateNode.containerInfo : null;
          e = null;
        } else t !== e && (e = null);
      }
    }
    return Cl = e, null;
  }
  function Id(e) {
    switch (e) {
      case "beforetoggle":
      case "cancel":
      case "click":
      case "close":
      case "contextmenu":
      case "copy":
      case "cut":
      case "auxclick":
      case "dblclick":
      case "dragend":
      case "dragstart":
      case "drop":
      case "focusin":
      case "focusout":
      case "input":
      case "invalid":
      case "keydown":
      case "keypress":
      case "keyup":
      case "mousedown":
      case "mouseup":
      case "paste":
      case "pause":
      case "play":
      case "pointercancel":
      case "pointerdown":
      case "pointerup":
      case "ratechange":
      case "reset":
      case "resize":
      case "seeked":
      case "submit":
      case "toggle":
      case "touchcancel":
      case "touchend":
      case "touchstart":
      case "volumechange":
      case "change":
      case "selectionchange":
      case "textInput":
      case "compositionstart":
      case "compositionend":
      case "compositionupdate":
      case "beforeblur":
      case "afterblur":
      case "beforeinput":
      case "blur":
      case "fullscreenchange":
      case "focus":
      case "hashchange":
      case "popstate":
      case "select":
      case "selectstart":
        return 2;
      case "drag":
      case "dragenter":
      case "dragexit":
      case "dragleave":
      case "dragover":
      case "mousemove":
      case "mouseout":
      case "mouseover":
      case "pointermove":
      case "pointerout":
      case "pointerover":
      case "scroll":
      case "touchmove":
      case "wheel":
      case "mouseenter":
      case "mouseleave":
      case "pointerenter":
      case "pointerleave":
        return 8;
      case "message":
        switch (uf()) {
          case Jo:
            return 2;
          case es:
            return 8;
          case xa:
          case cf:
            return 32;
          case ts:
            return 268435456;
          default:
            return 32;
        }
      default:
        return 32;
    }
  }
  var Qo = false, fn = null, pn = null, hn = null, pa = /* @__PURE__ */ new Map(), ha = /* @__PURE__ */ new Map(), mn = [], Nh = "mousedown mouseup touchcancel touchend touchstart auxclick dblclick pointercancel pointerdown pointerup dragend dragstart drop compositionend compositionstart keydown keypress keyup input textInput copy cut paste click change contextmenu reset".split(" ");
  function Ud(e, t) {
    switch (e) {
      case "focusin":
      case "focusout":
        fn = null;
        break;
      case "dragenter":
      case "dragleave":
        pn = null;
        break;
      case "mouseover":
      case "mouseout":
        hn = null;
        break;
      case "pointerover":
      case "pointerout":
        pa.delete(t.pointerId);
        break;
      case "gotpointercapture":
      case "lostpointercapture":
        ha.delete(t.pointerId);
    }
  }
  function ma(e, t, n, r, a, l) {
    return e === null || e.nativeEvent !== l ? (e = { blockedOn: t, domEventName: n, eventSystemFlags: r, nativeEvent: l, targetContainers: [a] }, t !== null && (t = Dn(t), t !== null && Md(t)), e) : (e.eventSystemFlags |= r, t = e.targetContainers, a !== null && t.indexOf(a) === -1 && t.push(a), e);
  }
  function Eh(e, t, n, r, a) {
    switch (t) {
      case "focusin":
        return fn = ma(fn, e, t, n, r, a), true;
      case "dragenter":
        return pn = ma(pn, e, t, n, r, a), true;
      case "mouseover":
        return hn = ma(hn, e, t, n, r, a), true;
      case "pointerover":
        var l = a.pointerId;
        return pa.set(l, ma(pa.get(l) || null, e, t, n, r, a)), true;
      case "gotpointercapture":
        return l = a.pointerId, ha.set(l, ma(ha.get(l) || null, e, t, n, r, a)), true;
    }
    return false;
  }
  function Bd(e) {
    var t = Fn(e.target);
    if (t !== null) {
      var n = k(t);
      if (n !== null) {
        if (t = n.tag, t === 13) {
          if (t = se(n), t !== null) {
            e.blockedOn = t, vf(e.priority, function() {
              if (n.tag === 13) {
                var r = ut();
                r = Il(r);
                var a = Yn(n, r);
                a !== null && ct(a, n, r), $o(n, r);
              }
            });
            return;
          }
        } else if (t === 3 && n.stateNode.current.memoizedState.isDehydrated) {
          e.blockedOn = n.tag === 3 ? n.stateNode.containerInfo : null;
          return;
        }
      }
    }
    e.blockedOn = null;
  }
  function zl(e) {
    if (e.blockedOn !== null) return false;
    for (var t = e.targetContainers; 0 < t.length; ) {
      var n = Ko(e.nativeEvent);
      if (n === null) {
        n = e.nativeEvent;
        var r = new n.constructor(n.type, n);
        Yl = r, n.target.dispatchEvent(r), Yl = null;
      } else return t = Dn(n), t !== null && Md(t), e.blockedOn = n, false;
      t.shift();
    }
    return true;
  }
  function Wd(e, t, n) {
    zl(e) && n.delete(t);
  }
  function _h() {
    Qo = false, fn !== null && zl(fn) && (fn = null), pn !== null && zl(pn) && (pn = null), hn !== null && zl(hn) && (hn = null), pa.forEach(Wd), ha.forEach(Wd);
  }
  function Pl(e, t) {
    e.blockedOn === t && (e.blockedOn = null, Qo || (Qo = true, m.unstable_scheduleCallback(m.unstable_NormalPriority, _h)));
  }
  var Ll = null;
  function Hd(e) {
    Ll !== e && (Ll = e, m.unstable_scheduleCallback(m.unstable_NormalPriority, function() {
      Ll === e && (Ll = null);
      for (var t = 0; t < e.length; t += 3) {
        var n = e[t], r = e[t + 1], a = e[t + 2];
        if (typeof r != "function") {
          if (Vo(r || n) === null) continue;
          break;
        }
        var l = Dn(n);
        l !== null && (e.splice(t, 3), t -= 3, Ki(l, { pending: true, data: a, method: n.method, action: r }, r, a));
      }
    }));
  }
  function ga(e) {
    function t(d) {
      return Pl(d, e);
    }
    fn !== null && Pl(fn, e), pn !== null && Pl(pn, e), hn !== null && Pl(hn, e), pa.forEach(t), ha.forEach(t);
    for (var n = 0; n < mn.length; n++) {
      var r = mn[n];
      r.blockedOn === e && (r.blockedOn = null);
    }
    for (; 0 < mn.length && (n = mn[0], n.blockedOn === null); ) Bd(n), n.blockedOn === null && mn.shift();
    if (n = (e.ownerDocument || e).$$reactFormReplay, n != null) for (r = 0; r < n.length; r += 3) {
      var a = n[r], l = n[r + 1], i = a[Ge] || null;
      if (typeof l == "function") i || Hd(n);
      else if (i) {
        var u = null;
        if (l && l.hasAttribute("formAction")) {
          if (a = l, i = l[Ge] || null) u = i.formAction;
          else if (Vo(a) !== null) continue;
        } else u = i.action;
        typeof u == "function" ? n[r + 1] = u : (n.splice(r, 3), r -= 3), Hd(n);
      }
    }
  }
  function Yo(e) {
    this._internalRoot = e;
  }
  Tl.prototype.render = Yo.prototype.render = function(e) {
    var t = this._internalRoot;
    if (t === null) throw Error(o(409));
    var n = t.current, r = ut();
    Fd(n, r, e, t, null, null);
  }, Tl.prototype.unmount = Yo.prototype.unmount = function() {
    var e = this._internalRoot;
    if (e !== null) {
      this._internalRoot = null;
      var t = e.containerInfo;
      Fd(e.current, 2, null, e, null, null), pl(), t[On] = null;
    }
  };
  function Tl(e) {
    this._internalRoot = e;
  }
  Tl.prototype.unstable_scheduleHydration = function(e) {
    if (e) {
      var t = is();
      e = { blockedOn: null, target: e, priority: t };
      for (var n = 0; n < mn.length && t !== 0 && t < mn[n].priority; n++) ;
      mn.splice(n, 0, e), n === 0 && Bd(e);
    }
  };
  var $d = P.version;
  if ($d !== "19.1.1") throw Error(o(527, $d, "19.1.1"));
  z.findDOMNode = function(e) {
    var t = e._reactInternals;
    if (t === void 0) throw typeof e.render == "function" ? Error(o(188)) : (e = Object.keys(e).join(","), Error(o(268, e)));
    return e = D(t), e = e !== null ? N(e) : null, e = e === null ? null : e.stateNode, e;
  };
  var jh = { bundleType: 0, version: "19.1.1", rendererPackageName: "react-dom", currentDispatcherRef: x, reconcilerVersion: "19.1.1" };
  if (typeof __REACT_DEVTOOLS_GLOBAL_HOOK__ < "u") {
    var Al = __REACT_DEVTOOLS_GLOBAL_HOOK__;
    if (!Al.isDisabled && Al.supportsFiber) try {
      xr = Al.inject(jh), nt = Al;
    } catch {
    }
  }
  return va.createRoot = function(e, t) {
    if (!c(e)) throw Error(o(299));
    var n = false, r = "", a = ic, l = oc, i = sc, u = null;
    return t != null && (t.unstable_strictMode === true && (n = true), t.identifierPrefix !== void 0 && (r = t.identifierPrefix), t.onUncaughtError !== void 0 && (a = t.onUncaughtError), t.onCaughtError !== void 0 && (l = t.onCaughtError), t.onRecoverableError !== void 0 && (i = t.onRecoverableError), t.unstable_transitionCallbacks !== void 0 && (u = t.unstable_transitionCallbacks)), t = Rd(e, 1, false, null, null, n, r, a, l, i, u, null), e[On] = t.current, Lo(e), new Yo(t);
  }, va.hydrateRoot = function(e, t, n) {
    if (!c(e)) throw Error(o(299));
    var r = false, a = "", l = ic, i = oc, u = sc, d = null, y = null;
    return n != null && (n.unstable_strictMode === true && (r = true), n.identifierPrefix !== void 0 && (a = n.identifierPrefix), n.onUncaughtError !== void 0 && (l = n.onUncaughtError), n.onCaughtError !== void 0 && (i = n.onCaughtError), n.onRecoverableError !== void 0 && (u = n.onRecoverableError), n.unstable_transitionCallbacks !== void 0 && (d = n.unstable_transitionCallbacks), n.formState !== void 0 && (y = n.formState)), t = Rd(e, 1, true, t, n ?? null, r, a, l, i, u, d, y), t.context = Od(null), n = t.current, r = ut(), r = Il(r), a = Xt(r), a.callback = null, Zt(n, a, r), n = r, t.current.lanes = n, kr(t, n), jt(t), e[On] = t.current, Lo(e), new Tl(t);
  }, va.version = "19.1.1", va;
}
var af;
function Dh() {
  if (af) return Go.exports;
  af = 1;
  function m() {
    if (!(typeof __REACT_DEVTOOLS_GLOBAL_HOOK__ > "u" || typeof __REACT_DEVTOOLS_GLOBAL_HOOK__.checkDCE != "function")) try {
      __REACT_DEVTOOLS_GLOBAL_HOOK__.checkDCE(m);
    } catch (P) {
      console.error(P);
    }
  }
  return m(), Go.exports = Fh(), Go.exports;
}
var Mh = Dh();
const lf = () => {
  const [m, P] = G.useState("StoreSEO"), [_, o] = G.useState(null), [c, k] = G.useState([]), [se, ie] = G.useState(false), [D, N] = G.useState(null), [R, K] = G.useState(0), [O, V] = G.useState("this_month"), [W, fe] = G.useState({ start: "", end: "" }), [ae, we] = G.useState(false), Fe = (C) => {
    P(C);
  }, Ee = async (C) => {
    if (C) {
      ie(true), N(null), K(0);
      try {
        K(1), await new Promise((pe) => setTimeout(pe, 800)), K(2);
        const L = await fetch(`/backend/api/enhanced-analytics.php?app=${encodeURIComponent(C)}`);
        K(3), await new Promise((pe) => setTimeout(pe, 500));
        const J = await L.json();
        J.success ? (o(J.data), await Ae(C, O)) : N(J.error || "Failed to fetch analytics data");
      } catch (L) {
        N("Network error: " + L.message);
      } finally {
        ie(false), K(0);
      }
    }
  }, Ae = async (C, L) => {
    if (C) try {
      const J = L === "last_90_days" ? 30 : L === "all" ? 50 : L === "custom" ? 25 : 15;
      let pe = `/backend/api/access-reviews-cached.php?app=${encodeURIComponent(C)}&page=1&limit=${J}&_t=${Date.now()}&_cache_bust=${Math.random()}`;
      L === "custom" && W.start && W.end ? pe += `&start_date=${W.start}&end_date=${W.end}` : L !== "all" && (pe += `&filter=${L}`), console.log("Fetching reviews with filter:", L, "URL:", pe);
      const ke = await (await fetch(pe)).json();
      console.log("Filter response:", L, "Reviews count:", ke.data?.reviews?.length), ke.success && ke.data && ke.data.reviews && k(ke.data.reviews);
    } catch (J) {
      console.error("Error fetching filtered reviews:", J);
    }
  };
  G.useEffect(() => {
    m && (Ee(m), (O !== "custom" || W.start && W.end) && Ae(m, O));
  }, [m]), G.useEffect(() => {
    console.log("Filter changed to:", O, "for app:", m), m && O !== "custom" && Ae(m, O);
  }, [O]), G.useEffect(() => {
    m && O === "custom" && W.start && W.end && Ae(m, O);
  }, [W]);
  const X = (C) => new Date(C).toLocaleDateString("en-US", { year: "numeric", month: "short", day: "numeric" }), Ne = (C) => {
    if (!C || C === "Unknown") return "Unknown";
    const L = C.split(`
`).map((J) => J.trim()).filter((J) => J.length > 0).pop();
    return { "United States": "🇺🇸 United States", Canada: "🇨🇦 Canada", "United Kingdom": "🇬🇧 United Kingdom", Australia: "🇦🇺 Australia", Germany: "🇩🇪 Germany", France: "🇫🇷 France", India: "🇮🇳 India", Brazil: "🇧🇷 Brazil", Netherlands: "🇳🇱 Netherlands", Spain: "🇪🇸 Spain", Italy: "🇮🇹 Italy", Japan: "🇯🇵 Japan", "South Korea": "🇰🇷 South Korea", Mexico: "🇲🇽 Mexico", Argentina: "🇦🇷 Argentina", Switzerland: "🇨🇭 Switzerland", Austria: "🇦🇹 Austria", Ireland: "🇮🇪 Ireland" }[L] || `🌍 ${L}`;
  }, T = (C) => "★".repeat(C) + "☆".repeat(5 - C);
  return s.jsxs("div", { className: "analytics-dashboard", children: [s.jsxs("div", { className: "analytics-header", children: [s.jsxs("div", { className: "analytics-title", children: [s.jsx("h1", { children: "📊 Analytics Dashboard" }), s.jsx("p", { children: "Real-time insights from Shopify app reviews" })] }), s.jsx("div", { className: "app-selector-container", children: s.jsxs("select", { value: m, onChange: (C) => Fe(C.target.value), className: "app-selector-dropdown", children: [s.jsx("option", { value: "", children: "Select an app..." }), s.jsx("option", { value: "StoreSEO", children: "StoreSEO" }), s.jsx("option", { value: "StoreFAQ", children: "StoreFAQ" }), s.jsx("option", { value: "EasyFlow", children: "EasyFlow" }), s.jsx("option", { value: "BetterDocs FAQ Knowledge Base", children: "BetterDocs FAQ Knowledge Base" }), s.jsx("option", { value: "Vidify", children: "Vidify" }), s.jsx("option", { value: "TrustSync", children: "TrustSync" })] }) })] }), m ? s.jsx("div", { className: "analytics-main", children: se ? s.jsx("div", { className: "smart-loading-container", children: s.jsxs("div", { className: "loading-animation", children: [s.jsxs("div", { className: "shopify-loader", children: [s.jsx("div", { className: "loader-circle" }), s.jsx("div", { className: "loader-circle" }), s.jsx("div", { className: "loader-circle" })] }), s.jsxs("div", { className: "loading-app-info", children: [s.jsx("div", { className: "app-icon", children: "📱" }), s.jsxs("h3", { children: ["Analyzing ", m] })] }), s.jsxs("div", { className: "loading-steps", children: [s.jsxs("div", { className: `loading-step ${R >= 1 ? "active" : ""} ${R > 1 ? "completed" : ""}`, children: [s.jsx("div", { className: "step-icon", children: R > 1 ? "✅" : "🔍" }), s.jsx("span", { children: "Connecting to Shopify..." })] }), s.jsxs("div", { className: `loading-step ${R >= 2 ? "active" : ""} ${R > 2 ? "completed" : ""}`, children: [s.jsx("div", { className: "step-icon", children: R > 2 ? "✅" : "📊" }), s.jsx("span", { children: "Fetching review data..." })] }), s.jsxs("div", { className: `loading-step ${R >= 3 ? "active" : ""} ${R > 3 ? "completed" : ""}`, children: [s.jsx("div", { className: "step-icon", children: R > 3 ? "✅" : "⚡" }), s.jsx("span", { children: "Processing analytics..." })] })] }), s.jsxs("div", { className: "progress-container", children: [s.jsx("div", { className: "progress-bar", children: s.jsx("div", { className: "progress-fill" }) }), s.jsx("div", { className: "progress-text", children: "Loading real-time data..." })] })] }) }) : D ? s.jsxs("div", { className: "error-state", children: [s.jsx("div", { className: "error-icon", children: "⚠️" }), s.jsx("h3", { children: "Error Loading Data" }), s.jsx("p", { children: D }), s.jsx("button", { onClick: () => Ee(m), className: "retry-btn", children: "🔄 Retry" })] }) : _ ? s.jsxs(s.Fragment, { children: [s.jsxs("div", { className: "stats-grid", children: [s.jsxs("div", { className: "stat-card this-month", children: [s.jsx("div", { className: "stat-icon", children: "📈" }), s.jsxs("div", { className: "stat-content", children: [s.jsx("h3", { children: "This Month" }), s.jsx("div", { className: "stat-value", children: _.this_month_count }), s.jsx("div", { className: "stat-label", children: "Reviews" })] })] }), s.jsxs("div", { className: "stat-card last-30-days", children: [s.jsx("div", { className: "stat-icon", children: "📅" }), s.jsxs("div", { className: "stat-content", children: [s.jsx("h3", { children: "Last 30 Days" }), s.jsx("div", { className: "stat-value", children: _.last_30_days_count }), s.jsx("div", { className: "stat-label", children: "Reviews" })] })] }), s.jsxs("div", { className: "stat-card total-reviews", children: [s.jsx("div", { className: "stat-icon", children: "📊" }), s.jsxs("div", { className: "stat-content", children: [s.jsx("h3", { children: "Total Reviews" }), s.jsx("div", { className: "stat-value", children: _.rating_distribution_total || _.total_reviews || 0 }), s.jsx("div", { className: "stat-label", children: "All Time" })] })] }), s.jsxs("div", { className: "stat-card average-rating", children: [s.jsx("div", { className: "stat-icon", children: "⭐" }), s.jsxs("div", { className: "stat-content", children: [s.jsx("h3", { children: "Average Rating" }), s.jsx("div", { className: "stat-value", children: _.shopify_display_rating || _.average_rating }), s.jsx("div", { className: "stat-label", children: "Stars" })] })] })] }), s.jsxs("div", { className: "rating-distribution-section", children: [s.jsxs("div", { className: "section-header", children: [s.jsx("h2", { children: "📊 Rating Distribution" }), s.jsxs("div", { className: "data-source-info", children: [s.jsx("span", { className: "data-badge live-scraping", children: "🔴 Live from Shopify" }), _.rating_distribution_total && s.jsxs("span", { className: "total-analyzed", children: [_.rating_distribution_total, " reviews analyzed"] })] })] }), s.jsx("div", { className: "rating-bars", children: [5, 4, 3, 2, 1].map((C) => {
    const L = _.rating_distribution[C] || 0, J = _.rating_distribution_total || _.total_reviews || 0, pe = J > 0 ? (L / J * 100).toFixed(1) : 0;
    return s.jsxs("div", { className: "rating-bar", children: [s.jsxs("div", { className: "rating-label", children: [s.jsx("span", { className: "stars", children: T(C) }), s.jsx("span", { className: "rating-number", children: C })] }), s.jsx("div", { className: "bar-container", children: s.jsx("div", { className: "bar-fill", style: { width: `${pe}%` } }) }), s.jsxs("div", { className: "rating-stats", children: [s.jsx("span", { className: "count", children: L }), s.jsxs("span", { className: "percentage", children: ["(", pe, "%)"] })] })] }, C);
  }) })] }), s.jsxs("div", { className: "latest-reviews-section", children: [s.jsxs("div", { className: "section-header", children: [s.jsx("h2", { children: "📝 Reviews Details" }), s.jsx("div", { className: "reviews-filter-container", children: s.jsxs("select", { value: O, onChange: (C) => {
    const L = C.target.value;
    V(L), we(L === "custom");
  }, className: "reviews-filter-select", children: [s.jsx("option", { value: "all", children: "All Reviews" }), s.jsx("option", { value: "last_30_days", children: "Last 30 Days" }), s.jsx("option", { value: "this_month", children: "This Month" }), s.jsx("option", { value: "last_month", children: "Last Month" }), s.jsx("option", { value: "last_90_days", children: "Last 90 Days" }), s.jsx("option", { value: "custom", children: "Custom Date Range" })] }) })] }), ae && s.jsxs("div", { className: "custom-date-container", children: [s.jsxs("div", { className: "custom-date-header", children: [s.jsx("span", { className: "date-icon", children: "📅" }), s.jsx("h4", { children: "Select Date Range" })] }), s.jsxs("div", { className: "custom-date-inputs", children: [s.jsxs("div", { className: "date-input-group", children: [s.jsx("label", { htmlFor: "start-date", children: "From" }), s.jsx("input", { id: "start-date", type: "date", value: W.start, onChange: (C) => fe((L) => ({ ...L, start: C.target.value })), className: "date-input", max: W.end || (/* @__PURE__ */ new Date()).toISOString().split("T")[0] })] }), s.jsx("div", { className: "date-separator", children: s.jsx("span", { children: "→" }) }), s.jsxs("div", { className: "date-input-group", children: [s.jsx("label", { htmlFor: "end-date", children: "To" }), s.jsx("input", { id: "end-date", type: "date", value: W.end, onChange: (C) => fe((L) => ({ ...L, end: C.target.value })), className: "date-input", min: W.start, max: (/* @__PURE__ */ new Date()).toISOString().split("T")[0] })] })] }), s.jsxs("div", { className: "date-quick-actions", children: [s.jsx("button", { onClick: () => {
    const C = /* @__PURE__ */ new Date(), L = new Date(C.getTime() - 10080 * 60 * 1e3);
    fe({ start: L.toISOString().split("T")[0], end: C.toISOString().split("T")[0] });
  }, className: "quick-date-btn", children: "Last 7 Days" }), s.jsx("button", { onClick: () => {
    const C = /* @__PURE__ */ new Date(), L = new Date(C.getTime() - 720 * 60 * 60 * 1e3);
    fe({ start: L.toISOString().split("T")[0], end: C.toISOString().split("T")[0] });
  }, className: "quick-date-btn", children: "Last 30 Days" }), s.jsx("button", { onClick: () => {
    fe({ start: "", end: "" });
  }, className: "quick-date-btn clear-btn", children: "Clear" })] })] }), c.length > 0 && s.jsxs("div", { className: "date-range-indicator", children: [s.jsx("span", { className: "range-label", children: "Showing reviews from:" }), s.jsxs("span", { className: "date-range", children: [c[c.length - 1]?.review_date, " to ", c[0]?.review_date] }), s.jsxs("span", { className: "review-count", children: ["(", c.length, " reviews displayed)"] })] }), s.jsx("div", { className: "reviews-list", children: c.length > 0 ? c.map((C, L) => s.jsxs("div", { className: "review-item", children: [s.jsxs("div", { className: "review-header", children: [s.jsxs("div", { className: "review-meta", children: [s.jsx("span", { className: "store-name", children: C.store_name }), s.jsx("span", { className: "review-date", children: X(C.review_date) }), s.jsx("span", { className: "country", children: Ne(C.country_name) })] }), s.jsx("div", { className: "review-rating", children: T(C.rating) })] }), s.jsx("div", { className: "review-content", children: s.jsx("p", { children: C.review_content }) })] }, L)) : s.jsx("div", { className: "no-reviews", children: s.jsx("p", { children: "No recent reviews found" }) }) })] })] }) : null }) : s.jsx("div", { className: "no-app-selected", children: s.jsxs("div", { className: "no-app-message", children: [s.jsx("div", { className: "no-app-icon", children: "📊" }), s.jsx("h2", { children: "Choose an app to analyze" }), s.jsx("p", { children: "Select an app from the dropdown above to view comprehensive analytics" })] }) })] });
}, Ih = () => {
  const m = [{ name: "StoreSEO", slug: "storeseo" }, { name: "StoreFAQ", slug: "storefaq" }, { name: "EasyFlow", slug: "product-options-4" }, { name: "TrustSync", slug: "customer-review-app" }, { name: "Vidify", slug: "vidify" }, { name: "BetterDocs FAQ Knowledge Base", slug: "betterdocs-knowledgebase" }], [P, _] = G.useState("StoreSEO"), [o, c] = G.useState([]), [k, se] = G.useState(null), [ie, D] = G.useState(true), [N, R] = G.useState(null), [K, O] = G.useState({ current_page: 1, total_pages: 0, total_items: 0, items_per_page: 15, has_next_page: false, has_prev_page: false, page_numbers: [] }), [V, W] = G.useState({ StoreSEO: 1, StoreFAQ: 1, EasyFlow: 1, TrustSync: 1, Vidify: 1, "BetterDocs FAQ Knowledge Base": 1 }), [fe, ae] = G.useState(null), [we, Fe] = G.useState(""), [Ee, Ae] = G.useState(0);
  G.useEffect(() => {
    X(P, V[P]);
  }, [P]);
  const X = async (U, oe = 1) => {
    D(true), R(null);
    try {
      const x = await fetch(`/backend/api/access-reviews-cached.php?app=${encodeURIComponent(U)}&page=${oe}&limit=15&_t=${Date.now()}&_cache_bust=${Math.random()}`);
      if (!x.ok) throw new Error(`HTTP error! status: ${x.status}`);
      const z = await x.json();
      if (z.success) c(z.data.reviews || []), O(z.data.pagination || {}), se(z.data.statistics || {});
      else throw new Error(z.error || "Failed to fetch reviews");
    } catch (x) {
      console.error("Error fetching reviews:", x), R(x.message), c([]);
    } finally {
      D(false);
    }
  }, Ne = (U) => {
    U !== P && (_(U), X(U, V[U]));
  }, T = (U) => {
    W((oe) => ({ ...oe, [P]: U })), X(P, U);
  }, C = (U) => {
    Ae(window.pageYOffset), ae(U.id), Fe(U.earned_by || "");
  }, L = async (U) => {
    try {
      const oe = await (await fetch("/backend/api/access-reviews-tabbed.php", { method: "POST", headers: { "Content-Type": "application/json" }, body: JSON.stringify({ review_id: U, earned_by: we.trim() }) })).json();
      oe.success ? (c((x) => x.map((z) => z.id === U ? { ...z, earned_by: we.trim() } : z)), ae(null), Fe(""), setTimeout(() => {
        window.scrollTo(0, Ee);
      }, 100)) : alert("Error updating assignment: " + oe.error);
    } catch (oe) {
      console.error("Error updating assignment:", oe), alert("Error updating assignment");
    }
  }, J = () => {
    ae(null), Fe(""), setTimeout(() => {
      window.scrollTo(0, Ee);
    }, 100);
  }, pe = (U) => !U || U === "1970-01-01" ? "Unknown Date" : new Date(U).toLocaleDateString("en-US", { year: "numeric", month: "short", day: "numeric" }), ke = (U) => {
    if (!U || U === "Unknown") return "Unknown";
    const oe = U.split(`
`).map((x) => x.trim()).filter((x) => x.length > 0).pop();
    return { "United States": "🇺🇸 United States", Canada: "🇨🇦 Canada", "United Kingdom": "🇬🇧 United Kingdom", Australia: "🇦🇺 Australia", Germany: "🇩🇪 Germany", France: "🇫🇷 France", India: "🇮🇳 India", Brazil: "🇧🇷 Brazil", Netherlands: "🇳🇱 Netherlands", Spain: "🇪🇸 Spain", Italy: "🇮🇹 Italy", Japan: "🇯🇵 Japan", "South Korea": "🇰🇷 South Korea", Mexico: "🇲🇽 Mexico", Argentina: "🇦🇷 Argentina", Switzerland: "🇨🇭 Switzerland", Austria: "🇦🇹 Austria", Ireland: "🇮🇪 Ireland" }[oe] || `🌍 ${oe}`;
  }, Ct = (U) => {
    const oe = [], x = parseInt(U);
    if (isNaN(x) || x < 1 || x > 5) return s.jsx("span", { className: "invalid-rating", children: "❓" });
    for (let z = 1; z <= 5; z++) oe.push(s.jsx("span", { className: z <= x ? "star filled" : "star", children: "★" }, z));
    return oe;
  };
  return s.jsxs("div", { className: "access-container", children: [s.jsxs("div", { className: "access-header", children: [s.jsx("h1", { children: "Access Reviews - App Tabs" }), s.jsx("p", { children: "Browse reviews with name assignments by app" }), k && s.jsxs("div", { className: "tab-statistics", children: [s.jsxs("div", { className: "stat-item", children: [s.jsx("span", { className: "stat-label", children: "Total Reviews:" }), s.jsx("span", { className: "stat-value", children: k.total_reviews })] }), s.jsxs("div", { className: "stat-item", children: [s.jsx("span", { className: "stat-label", children: "Assigned:" }), s.jsx("span", { className: "stat-value", children: k.assigned_reviews })] }), s.jsxs("div", { className: "stat-item", children: [s.jsx("span", { className: "stat-label", children: "Unassigned:" }), s.jsx("span", { className: "stat-value", children: k.unassigned_reviews })] }), s.jsxs("div", { className: "stat-item", children: [s.jsx("span", { className: "stat-label", children: "Avg Rating:" }), s.jsxs("span", { className: "stat-value", children: [k.avg_rating, "★"] })] }), k.cache_status && s.jsxs("div", { className: "stat-item", children: [s.jsx("span", { className: "stat-label", children: "Data:" }), s.jsx("span", { className: `stat-value cache-${k.cache_status}`, children: k.cache_status === "hit" ? "⚡ Cached" : "🔄 Fresh" })] })] })] }), s.jsx("div", { className: "tab-navigation", children: m.map((U) => s.jsx("button", { className: `tab-button ${P === U.name ? "active" : ""}`, onClick: () => Ne(U.name), children: U.name }, U.name)) }), s.jsx("div", { className: "tab-content", children: ie ? s.jsx("div", { className: "loading-message", children: s.jsxs("p", { children: ["Loading ", P, " reviews..."] }) }) : N ? s.jsxs("div", { className: "error-message", children: [s.jsxs("p", { children: ["Error: ", N] }), s.jsx("button", { onClick: () => X(P, V[P]), children: "Retry" })] }) : s.jsxs(s.Fragment, { children: [s.jsxs("div", { className: "reviews-header", children: [s.jsxs("h2", { children: [P, " Reviews (", K.total_items, " assigned)"] }), s.jsxs("p", { children: ["Page ", K.current_page, " of ", K.total_pages] })] }), o.length === 0 ? s.jsx("div", { className: "no-reviews", children: s.jsxs("p", { children: ["No assigned reviews found for ", P] }) }) : s.jsx("div", { className: "reviews-list", children: o.map((U) => s.jsxs("div", { className: "review-item", children: [s.jsxs("div", { className: "review-header", children: [s.jsxs("div", { className: "review-meta", children: [s.jsx("span", { className: "store-name", children: U.store_name }), s.jsx("span", { className: "review-date", children: pe(U.review_date) }), s.jsx("span", { className: "country", children: ke(U.country_name) })] }), s.jsx("div", { className: "review-rating", children: Ct(U.rating) })] }), s.jsx("div", { className: "review-content", children: s.jsx("p", { children: U.review_content }) }), s.jsxs("div", { className: "review-assignment", children: [s.jsx("label", { children: "Assigned to:" }), fe === U.id ? s.jsxs("div", { className: "edit-assignment", children: [s.jsx("input", { type: "text", value: we, onChange: (oe) => Fe(oe.target.value), placeholder: "Enter name", className: "assignment-input", autoFocus: true }), s.jsx("button", { onClick: () => L(U.id), className: "save-btn", children: "Save" }), s.jsx("button", { onClick: J, className: "cancel-btn", children: "Cancel" })] }) : s.jsx("span", { className: "assignment-value clickable", onClick: () => C(U), title: "Click to edit", children: U.earned_by || "Unassigned" })] })] }, U.id)) }), K.total_pages > 1 && s.jsxs("div", { className: "pagination", children: [s.jsx("button", { onClick: () => T(K.current_page - 1), disabled: !K.has_prev_page, className: "pagination-btn", children: "Previous" }), K.page_numbers.map((U) => s.jsx("button", { onClick: () => T(U), className: `pagination-btn ${U === K.current_page ? "active" : ""}`, children: U }, U)), s.jsx("button", { onClick: () => T(K.current_page + 1), disabled: !K.has_next_page, className: "pagination-btn", children: "Next" })] })] }) })] });
}, Uh = () => {
  const m = (T) => {
    if (!T || T === "Unknown") return "Unknown";
    const C = T.split(`
`).map((L) => L.trim()).filter((L) => L.length > 0).pop();
    return { "United States": "🇺🇸 United States", Canada: "🇨🇦 Canada", "United Kingdom": "🇬🇧 United Kingdom", Australia: "🇦🇺 Australia", Germany: "🇩🇪 Germany", France: "🇫🇷 France", "South Africa": "🇿🇦 South Africa", India: "🇮🇳 India", Japan: "🇯🇵 Japan", Singapore: "🇸🇬 Singapore", "Costa Rica": "🇨🇷 Costa Rica", Netherlands: "🇳🇱 Netherlands", Sweden: "🇸🇪 Sweden", Norway: "🇳🇴 Norway", Denmark: "🇩🇰 Denmark", Finland: "🇫🇮 Finland", Belgium: "🇧🇪 Belgium", Switzerland: "🇨🇭 Switzerland", Austria: "🇦🇹 Austria", Ireland: "🇮🇪 Ireland" }[C] || `🌍 ${C}`;
  }, [P, _] = G.useState([]), [o, c] = G.useState(""), [k, se] = G.useState([]), [ie, D] = G.useState([]), [N, R] = G.useState(false), [K, O] = G.useState(false), [V, W] = G.useState(null), [fe, ae] = G.useState(null), [we, Fe] = G.useState("last_30_days");
  G.useEffect(() => {
    Ee();
  }, []), G.useEffect(() => {
    o && (Ae(o), X(o));
  }, [o, we]);
  const Ee = async () => {
    try {
      const T = await fetch("/backend/api/apps.php");
      if (!T.ok) throw new Error("Failed to fetch apps");
      const C = await T.json();
      _(C), C.length > 0 && c(C[0]);
    } catch (T) {
      W("Failed to load apps"), console.error("Error fetching apps:", T);
    }
  }, Ae = async (T) => {
    R(true), W(null);
    try {
      const C = `_t=${Date.now()}&_cache_bust=${Math.random()}`, L = await fetch(`/backend/api/agent-stats.php?app_name=${encodeURIComponent(T)}&filter=${we}&${C}`);
      if (!L.ok) throw new Error("Failed to fetch agent stats");
      const J = await L.json();
      J.message === "no_assignments" ? (se([]), W(`📊 ${J.info} You can assign reviews in the Access Review (Tabs) page.`)) : se(J);
    } catch (C) {
      W("Failed to load agent statistics"), console.error("Error fetching agent stats:", C);
    } finally {
      R(false);
    }
  }, X = async (T) => {
    O(true), ae(null);
    try {
      const C = `_t=${Date.now()}&_cache_bust=${Math.random()}`, L = await fetch(`/backend/api/country-stats.php?app_name=${encodeURIComponent(T)}&filter=${we}&${C}`);
      if (!L.ok) throw new Error("Failed to fetch country stats");
      const J = await L.json();
      if (J.success) D(J.country_stats || []);
      else throw new Error(J.message || "Failed to fetch country stats");
    } catch (C) {
      ae("Failed to load country statistics"), console.error("Error fetching country stats:", C), D([]);
    } finally {
      O(false);
    }
  }, Ne = (T) => {
    if (!T) return "";
    const C = { "BetterDocs FAQ": "BetterDocs FAQ", StoreFAQ: "StoreFAQ", StoreSEO: "StoreSEO", EasyFlow: "EasyFlow", TrustSync: "TrustSync" };
    return C[T] ? C[T] : T.split(" ").map((L) => L.charAt(0).toUpperCase() + L.slice(1).toLowerCase()).join(" ");
  };
  return s.jsxs("div", { className: "review-count-page", style: { minHeight: "100vh", background: "linear-gradient(135deg, #667eea 0%, #764ba2 100%)", padding: "20px 0" }, children: [s.jsx("style", { children: `
          @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
          }

          @keyframes fadeInUp {
            from {
              opacity: 0;
              transform: translateY(30px);
            }
            to {
              opacity: 1;
              transform: translateY(0);
            }
          }

          .stats-grid > div {
            animation: fadeInUp 0.6s ease forwards;
          }

          .stats-grid > div:nth-child(1) { animation-delay: 0.1s; }
          .stats-grid > div:nth-child(2) { animation-delay: 0.2s; }
          .stats-grid > div:nth-child(3) { animation-delay: 0.3s; }
          .stats-grid > div:nth-child(4) { animation-delay: 0.4s; }
          .stats-grid > div:nth-child(5) { animation-delay: 0.5s; }
          .stats-grid > div:nth-child(6) { animation-delay: 0.6s; }
        ` }), s.jsxs("div", { className: "container", style: { padding: "20px", maxWidth: "1400px", margin: "0 auto" }, children: [s.jsxs("div", { className: "page-header", style: { marginBottom: "40px", textAlign: "center", background: "linear-gradient(135deg, #667eea 0%, #764ba2 100%)", padding: "40px 20px", borderRadius: "20px", color: "white", position: "relative", overflow: "hidden" }, children: [s.jsx("div", { style: { position: "absolute", top: "-50%", left: "-50%", width: "200%", height: "200%", background: "radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%)", pointerEvents: "none" } }), s.jsxs("div", { style: { position: "relative", zIndex: 1 }, children: [s.jsx("div", { style: { fontSize: "3rem", marginBottom: "10px" }, children: "📊" }), s.jsx("h1", { style: { fontSize: "3rem", fontWeight: "bold", color: "white", marginBottom: "15px", textShadow: "0 4px 8px rgba(0,0,0,0.3)" }, children: "Appwise Reviews Dashboard" }), s.jsx("p", { style: { fontSize: "1.2rem", color: "rgba(255,255,255,0.9)", marginBottom: "0", maxWidth: "600px", margin: "0 auto" }, children: "Track and analyze support agent performance with comprehensive review statistics" })] })] }), s.jsxs("div", { className: "two-section-layout", style: { display: "grid", gridTemplateColumns: "300px 1fr", gap: "30px", height: "calc(100vh - 200px)" }, children: [s.jsxs("div", { className: "app-selection-section", style: { background: "rgba(255, 255, 255, 0.95)", backdropFilter: "blur(10px)", borderRadius: "20px", padding: "30px", boxShadow: "0 8px 32px rgba(0,0,0,0.1)", height: "fit-content", border: "1px solid rgba(255,255,255,0.2)" }, children: [s.jsxs("div", { style: { textAlign: "center", marginBottom: "30px" }, children: [s.jsx("div", { style: { fontSize: "2.5rem", marginBottom: "10px" }, children: "🎯" }), s.jsx("h3", { style: { fontSize: "1.5rem", fontWeight: "bold", color: "#333", marginBottom: "8px" }, children: "Select Application" }), s.jsx("p", { style: { fontSize: "0.9rem", color: "#666", margin: "0" }, children: "Choose an app to view agent statistics" })] }), o && s.jsxs("div", { style: { background: "linear-gradient(135deg, #667eea 0%, #764ba2 100%)", padding: "20px", borderRadius: "16px", marginBottom: "25px", color: "white", textAlign: "center", position: "relative", overflow: "hidden" }, children: [s.jsx("div", { style: { position: "absolute", top: "-50%", right: "-50%", width: "200%", height: "200%", background: "radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%)", pointerEvents: "none" } }), s.jsxs("div", { style: { position: "relative", zIndex: 1 }, children: [s.jsx("div", { style: { fontSize: "0.85rem", color: "rgba(255,255,255,0.8)", marginBottom: "8px", textTransform: "uppercase", letterSpacing: "1px" }, children: "Currently Analyzing" }), s.jsx("div", { style: { fontSize: "1.3rem", fontWeight: "bold", color: "white", textShadow: "0 2px 4px rgba(0,0,0,0.3)" }, children: Ne(o) }), s.jsx("div", { style: { width: "40px", height: "2px", background: "rgba(255,255,255,0.5)", margin: "10px auto 0", borderRadius: "1px" } })] })] }), s.jsxs("div", { style: { marginBottom: "25px" }, children: [s.jsxs("div", { style: { textAlign: "center", marginBottom: "15px" }, children: [s.jsx("h4", { style: { fontSize: "1.1rem", fontWeight: "bold", color: "#333", marginBottom: "5px" }, children: "📅 Time Period" }), s.jsx("p", { style: { fontSize: "0.85rem", color: "#666", margin: "0" }, children: "Select data range" })] }), s.jsxs("div", { style: { display: "flex", flexDirection: "column", gap: "8px" }, children: [s.jsxs("button", { onClick: () => Fe("last_30_days"), style: { padding: "12px 16px", border: we === "last_30_days" ? "2px solid #667eea" : "2px solid #e2e8f0", borderRadius: "12px", background: we === "last_30_days" ? "linear-gradient(135deg, #667eea 0%, #764ba2 100%)" : "white", color: we === "last_30_days" ? "white" : "#333", cursor: "pointer", fontSize: "0.9rem", fontWeight: "500", transition: "all 0.3s ease", textAlign: "left" }, children: ["📊 Last 30 Days", s.jsx("div", { style: { fontSize: "0.75rem", opacity: 0.8, marginTop: "2px" }, children: "Recent performance" })] }), s.jsxs("button", { onClick: () => Fe("all_time"), style: { padding: "12px 16px", border: we === "all_time" ? "2px solid #667eea" : "2px solid #e2e8f0", borderRadius: "12px", background: we === "all_time" ? "linear-gradient(135deg, #667eea 0%, #764ba2 100%)" : "white", color: we === "all_time" ? "white" : "#333", cursor: "pointer", fontSize: "0.9rem", fontWeight: "500", transition: "all 0.3s ease", textAlign: "left" }, children: ["🏆 All Time", s.jsx("div", { style: { fontSize: "0.75rem", opacity: 0.8, marginTop: "2px" }, children: "Complete history" })] })] })] }), s.jsx("div", { className: "app-list", style: { display: "flex", flexDirection: "column", gap: "12px" }, children: P.map((T, C) => {
    const L = o === T, J = ["linear-gradient(135deg, #667eea 0%, #764ba2 100%)", "linear-gradient(135deg, #f093fb 0%, #f5576c 100%)", "linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)", "linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)", "linear-gradient(135deg, #fa709a 0%, #fee140 100%)", "linear-gradient(135deg, #a8edea 0%, #fed6e3 100%)"];
    return s.jsxs("button", { className: "custom-selection-button", onClick: () => c(T), style: { width: "100%", padding: "16px 20px", border: "none", borderRadius: "12px", background: L ? J[C % J.length] : "rgba(255,255,255,0.98)", color: L ? "white" : "#1a202c", cursor: "pointer", textAlign: "left", fontSize: "1rem", fontWeight: L ? "600" : "500", transition: "all 0.3s ease", position: "relative", overflow: "hidden", boxShadow: L ? "0 8px 25px rgba(0,0,0,0.15)" : "0 2px 8px rgba(0,0,0,0.08)", transform: L ? "translateY(-2px)" : "translateY(0)", outline: "none !important" }, onFocus: (pe) => {
      pe.target.style.outline = "none";
    }, onBlur: (pe) => {
      pe.target.style.outline = "none";
    }, children: [L && s.jsx("div", { style: { position: "absolute", top: "-50%", right: "-50%", width: "200%", height: "200%", background: "radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%)", pointerEvents: "none" } }), s.jsxs("div", { style: { position: "relative", zIndex: 1, display: "flex", alignItems: "center", justifyContent: "space-between" }, children: [s.jsx("span", { children: Ne(T) }), L && s.jsx("span", { style: { fontSize: "1.2rem" }, children: "✓" })] })] }, T);
  }) }), s.jsx("div", { style: { marginTop: "25px", padding: "15px", background: "rgba(102, 126, 234, 0.1)", borderRadius: "12px", textAlign: "center" }, children: s.jsxs("div", { style: { fontSize: "0.85rem", color: "#666" }, children: ["📈 ", P.length, " applications available for analysis"] }) })] }), s.jsxs("div", { className: "agent-stats-section", style: { backgroundColor: "white", borderRadius: "8px", padding: "20px", boxShadow: "0 2px 4px rgba(0,0,0,0.1)" }, children: [s.jsxs("h3", { style: { fontSize: "1.3rem", fontWeight: "bold", color: "#333", marginBottom: "20px", borderBottom: "2px solid #28a745", paddingBottom: "10px" }, children: ["Support Agent Statistics", o && s.jsxs("span", { style: { fontSize: "1rem", fontWeight: "normal", color: "#666" }, children: [" ", "for ", Ne(o), " (", we === "last_30_days" ? "Last 30 Days" : "All Time", ")"] })] }), N && s.jsxs("div", { style: { textAlign: "center", padding: "60px 40px", background: "linear-gradient(135deg, #f093fb 0%, #f5576c 100%)", borderRadius: "16px", color: "white" }, children: [s.jsx("div", { style: { fontSize: "4rem", marginBottom: "20px", animation: "pulse 2s infinite" }, children: "⏳" }), s.jsx("div", { style: { fontSize: "1.3rem", fontWeight: "500" }, children: "Loading agent statistics..." }), s.jsxs("div", { style: { fontSize: "1rem", marginTop: "10px", color: "rgba(255,255,255,0.8)" }, children: ["Analyzing performance data for ", Ne(o)] })] }), V && s.jsxs("div", { style: { background: "linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%)", color: "white", padding: "24px", borderRadius: "16px", textAlign: "center", boxShadow: "0 8px 32px rgba(255,107,107,0.3)" }, children: [s.jsx("div", { style: { fontSize: "3rem", marginBottom: "15px" }, children: "⚠️" }), s.jsx("div", { style: { fontSize: "1.2rem", fontWeight: "600", marginBottom: "8px" }, children: "Oops! Something went wrong" }), s.jsx("div", { style: { fontSize: "1rem", color: "rgba(255,255,255,0.9)" }, children: V })] }), !N && !V && k.length === 0 && o && s.jsxs("div", { style: { textAlign: "center", padding: "60px 40px", background: "linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)", borderRadius: "16px", color: "white" }, children: [s.jsx("div", { style: { fontSize: "4rem", marginBottom: "20px" }, children: "📊" }), s.jsx("div", { style: { fontSize: "1.4rem", fontWeight: "600", marginBottom: "10px" }, children: "No Data Available" }), s.jsxs("div", { style: { fontSize: "1.1rem", color: "rgba(255,255,255,0.9)", maxWidth: "400px", margin: "0 auto" }, children: ["No review data found for ", s.jsx("strong", { children: Ne(o) }), " in the last 30 days. Try selecting a different app or check back later."] })] }), !N && !V && k.length > 0 && s.jsxs(s.Fragment, { children: [s.jsxs("div", { style: { display: "grid", gridTemplateColumns: "repeat(auto-fit, minmax(200px, 1fr))", gap: "15px", marginBottom: "30px" }, children: [s.jsxs("div", { style: { background: "linear-gradient(135deg, #667eea 0%, #764ba2 100%)", padding: "20px", borderRadius: "12px", color: "white", textAlign: "center" }, children: [s.jsx("div", { style: { fontSize: "2rem", fontWeight: "bold" }, children: k.length }), s.jsx("div", { style: { fontSize: "0.9rem", opacity: 0.9 }, children: "Active Agents" })] }), s.jsxs("div", { style: { background: "linear-gradient(135deg, #f093fb 0%, #f5576c 100%)", padding: "20px", borderRadius: "12px", color: "white", textAlign: "center" }, children: [s.jsx("div", { style: { fontSize: "2rem", fontWeight: "bold" }, children: k.reduce((T, C) => T + C.review_count, 0) }), s.jsx("div", { style: { fontSize: "0.9rem", opacity: 0.9 }, children: "Total Reviews" })] }), s.jsxs("div", { style: { background: "linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)", padding: "20px", borderRadius: "12px", color: "white", textAlign: "center" }, children: [s.jsx("div", { style: { fontSize: "2rem", fontWeight: "bold" }, children: Math.round(k.reduce((T, C) => T + C.review_count, 0) / k.length) }), s.jsx("div", { style: { fontSize: "0.9rem", opacity: 0.9 }, children: "Avg per Agent" })] })] }), s.jsx("div", { className: "stats-grid", style: { display: "grid", gridTemplateColumns: "repeat(auto-fit, minmax(200px, 1fr))", gap: "12px", marginTop: "20px" }, children: k.sort((T, C) => C.review_count - T.review_count).map((T, C) => {
    const L = C === 0 && T.review_count > 0;
    return C < 3 && T.review_count >= 5, s.jsxs("div", { style: { background: "white", borderRadius: "8px", padding: "16px", color: "#333", border: L ? "2px solid #667eea" : "1px solid #e0e0e0", boxShadow: "0 2px 4px rgba(0,0,0,0.1)", transition: "all 0.2s ease", position: "relative" }, children: [L && s.jsx("div", { style: { position: "absolute", top: "8px", right: "8px", background: "#667eea", color: "white", padding: "2px 6px", borderRadius: "4px", fontSize: "0.7rem", fontWeight: "bold" }, children: "👑" }), s.jsx("div", { style: { fontSize: "1.1rem", fontWeight: "500", margin: "0 0 8px 0", color: "#333" }, children: T.agent_name }), s.jsxs("div", { style: { display: "flex", alignItems: "center", gap: "8px", color: "#666" }, children: [s.jsx("div", { style: { fontSize: "1.5rem", fontWeight: "bold", color: "#333" }, children: T.review_count }), s.jsx("div", { style: { fontSize: "0.9rem", color: "#666" }, children: "reviews" })] }), s.jsx("div", { style: { marginTop: "12px", height: "3px", background: "#f0f0f0", borderRadius: "2px", overflow: "hidden" }, children: s.jsx("div", { style: { height: "100%", background: L ? "#667eea" : "#4facfe", borderRadius: "2px", width: `${Math.min(T.review_count / Math.max(...k.map((J) => J.review_count)) * 100, 100)}%`, transition: "width 0.3s ease" } }) })] }, C);
  }) })] }), o && s.jsxs("div", { style: { background: "rgba(255, 255, 255, 0.95)", backdropFilter: "blur(10px)", borderRadius: "20px", padding: "30px", boxShadow: "0 8px 32px rgba(0,0,0,0.1)", border: "1px solid rgba(255,255,255,0.2)", marginTop: "30px" }, children: [s.jsxs("div", { style: { textAlign: "center", marginBottom: "30px" }, children: [s.jsx("div", { style: { fontSize: "2.5rem", marginBottom: "10px" }, children: "🌍" }), s.jsx("h3", { style: { fontSize: "1.8rem", fontWeight: "bold", color: "#333", marginBottom: "8px" }, children: "Country-wise Review Count" }), s.jsxs("p", { style: { fontSize: "1rem", color: "#666", margin: "0" }, children: ["Review distribution by country for ", Ne(o), " (", we === "last_30_days" ? "Last 30 Days" : "All Time", ")"] })] }), K && s.jsxs("div", { style: { textAlign: "center", padding: "60px 40px", background: "linear-gradient(135deg, #28a745 0%, #20c997 100%)", borderRadius: "16px", color: "white" }, children: [s.jsx("div", { style: { fontSize: "4rem", marginBottom: "20px", animation: "pulse 2s infinite" }, children: "🌍" }), s.jsx("div", { style: { fontSize: "1.3rem", fontWeight: "500" }, children: "Loading country statistics..." }), s.jsx("div", { style: { fontSize: "1rem", marginTop: "10px", color: "rgba(255,255,255,0.8)" }, children: "Analyzing global review distribution" })] }), fe && s.jsxs("div", { style: { background: "linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%)", color: "white", padding: "24px", borderRadius: "16px", textAlign: "center", boxShadow: "0 8px 32px rgba(255,107,107,0.3)" }, children: [s.jsx("div", { style: { fontSize: "3rem", marginBottom: "15px" }, children: "⚠️" }), s.jsx("div", { style: { fontSize: "1.2rem", fontWeight: "600", marginBottom: "8px" }, children: "Oops! Something went wrong" }), s.jsx("div", { style: { fontSize: "1rem", color: "rgba(255,255,255,0.9)" }, children: fe })] }), !K && !fe && ie.length === 0 && s.jsxs("div", { style: { textAlign: "center", padding: "60px 40px", background: "linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)", borderRadius: "16px", color: "white" }, children: [s.jsx("div", { style: { fontSize: "4rem", marginBottom: "20px" }, children: "🌐" }), s.jsx("div", { style: { fontSize: "1.4rem", fontWeight: "600", marginBottom: "10px" }, children: "No Country Data Available" }), s.jsxs("div", { style: { fontSize: "1.1rem", color: "rgba(255,255,255,0.9)", maxWidth: "400px", margin: "0 auto" }, children: ["No country-specific review data found for ", s.jsx("strong", { children: Ne(o) }), " ", we === "last_30_days" ? "in the last 30 days" : "in all time", "."] })] }), !K && !fe && ie.length > 0 && s.jsxs(s.Fragment, { children: [s.jsxs("div", { style: { display: "grid", gridTemplateColumns: "repeat(auto-fit, minmax(200px, 1fr))", gap: "15px", marginBottom: "30px" }, children: [s.jsxs("div", { style: { background: "linear-gradient(135deg, #28a745 0%, #20c997 100%)", padding: "20px", borderRadius: "12px", color: "white", textAlign: "center" }, children: [s.jsx("div", { style: { fontSize: "2rem", fontWeight: "bold" }, children: ie.length }), s.jsx("div", { style: { fontSize: "0.9rem", opacity: 0.9 }, children: "Countries" })] }), s.jsxs("div", { style: { background: "linear-gradient(135deg, #f093fb 0%, #f5576c 100%)", padding: "20px", borderRadius: "12px", color: "white", textAlign: "center" }, children: [s.jsx("div", { style: { fontSize: "2rem", fontWeight: "bold" }, children: ie.reduce((T, C) => T + C.review_count, 0) }), s.jsx("div", { style: { fontSize: "0.9rem", opacity: 0.9 }, children: "Total Reviews" })] }), s.jsxs("div", { style: { background: "linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)", padding: "20px", borderRadius: "12px", color: "white", textAlign: "center" }, children: [s.jsx("div", { style: { fontSize: "2rem", fontWeight: "bold" }, children: ie.length > 0 ? Math.round(ie.reduce((T, C) => T + C.review_count, 0) / ie.length) : 0 }), s.jsx("div", { style: { fontSize: "0.9rem", opacity: 0.9 }, children: "Avg per Country" })] })] }), s.jsx("div", { className: "country-stats-grid", style: { display: "grid", gridTemplateColumns: "repeat(auto-fit, minmax(300px, 1fr))", gap: "20px", marginTop: "20px" }, children: ie.sort((T, C) => C.review_count - T.review_count).map((T, C) => {
    const L = C === 0 && T.review_count > 0, J = C < 3 && T.review_count >= 3, pe = ["linear-gradient(135deg, #28a745 0%, #20c997 100%)", "linear-gradient(135deg, #f093fb 0%, #f5576c 100%)", "linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)", "linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)", "linear-gradient(135deg, #fa709a 0%, #fee140 100%)", "linear-gradient(135deg, #a8edea 0%, #fed6e3 100%)"];
    return s.jsxs("div", { style: { background: L ? "linear-gradient(135deg, #28a745 0%, #20c997 100%)" : J ? pe[C % pe.length] : "linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)", borderRadius: "16px", padding: "24px", color: "white", position: "relative", overflow: "hidden", cursor: "pointer", transition: "all 0.3s ease", boxShadow: "0 8px 32px rgba(0,0,0,0.1)", border: "1px solid rgba(255,255,255,0.2)" }, children: [s.jsx("div", { style: { position: "absolute", top: "-50%", right: "-50%", width: "200%", height: "200%", background: "radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%)", pointerEvents: "none" } }), L && s.jsx("div", { style: { position: "absolute", top: "16px", right: "16px", background: "rgba(255,215,0,0.9)", color: "#333", padding: "4px 8px", borderRadius: "12px", fontSize: "0.75rem", fontWeight: "bold", display: "flex", alignItems: "center", gap: "4px" }, children: "👑 #1" }), J && !L && s.jsxs("div", { style: { position: "absolute", top: "16px", right: "16px", background: "rgba(255,255,255,0.2)", color: "white", padding: "4px 8px", borderRadius: "12px", fontSize: "0.75rem", fontWeight: "bold" }, children: ["⭐ Top ", C + 1] }), s.jsx("div", { style: { width: "60px", height: "60px", borderRadius: "50%", background: "rgba(255,255,255,0.2)", display: "flex", alignItems: "center", justifyContent: "center", fontSize: "24px", marginBottom: "16px", border: "2px solid rgba(255,255,255,0.3)" }, children: "🌍" }), s.jsx("h4", { style: { fontSize: "1.3rem", fontWeight: "600", margin: "0 0 8px 0", color: "white", textShadow: "0 2px 4px rgba(0,0,0,0.3)" }, children: m(T.country_name) }), s.jsxs("div", { style: { display: "flex", alignItems: "center", gap: "8px", marginBottom: "12px" }, children: [s.jsx("div", { style: { fontSize: "2.5rem", fontWeight: "bold", color: "white", textShadow: "0 2px 4px rgba(0,0,0,0.3)" }, children: T.review_count }), s.jsxs("div", { style: { fontSize: "1rem", color: "rgba(255,255,255,0.9)", fontWeight: "500" }, children: ["reviews", s.jsx("br", {}), s.jsxs("span", { style: { fontSize: "0.9rem" }, children: ["(", T.percentage, "%)"] })] })] }), s.jsxs("div", { style: { display: "flex", alignItems: "center", gap: "8px", fontSize: "0.9rem", color: "rgba(255,255,255,0.8)" }, children: [s.jsx("span", { children: "📊" }), s.jsx("span", { children: T.percentage >= 20 ? "Major Market" : T.percentage >= 10 ? "Significant Market" : T.percentage >= 5 ? "Growing Market" : "Emerging Market" })] }), s.jsx("div", { style: { marginTop: "16px", height: "4px", background: "rgba(255,255,255,0.2)", borderRadius: "2px", overflow: "hidden" }, children: s.jsx("div", { style: { height: "100%", background: "rgba(255,255,255,0.8)", borderRadius: "2px", width: `${Math.min(T.review_count / Math.max(...ie.map((ke) => ke.review_count)) * 100, 100)}%`, transition: "width 0.3s ease" } }) })] }, T.country_name);
  }) })] })] })] })] })] })] });
}, Bh = () => {
  const [m, P] = G.useState([]), [_, o] = G.useState(""), [c, k] = G.useState([]), [se, ie] = G.useState(false), [D, N] = G.useState(null);
  G.useEffect(() => {
    R();
  }, []), G.useEffect(() => {
    _ && K(_);
  }, [_]);
  const R = async () => {
    try {
      const O = await fetch("/backend/api/reviewers.php");
      if (!O.ok) throw new Error("Failed to fetch reviewers");
      const V = await O.json();
      P(V), V.length > 0 && o(V[0]);
    } catch (O) {
      N("Failed to load reviewers"), console.error("Error fetching reviewers:", O);
    }
  }, K = async (O) => {
    ie(true), N(null);
    try {
      const V = await fetch(`/backend/api/reviewer-stats.php?reviewer_name=${encodeURIComponent(O)}`);
      if (!V.ok) throw new Error("Failed to fetch reviewer stats");
      const W = await V.json();
      k(W);
    } catch (V) {
      N("Failed to load reviewer statistics"), console.error("Error fetching reviewer stats:", V);
    } finally {
      ie(false);
    }
  };
  return s.jsxs("div", { className: "review-credit-page", style: { minHeight: "100vh", background: "linear-gradient(135deg, #667eea 0%, #764ba2 100%)", padding: "20px 0" }, children: [s.jsx("style", { children: `
          @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
          }

          @keyframes fadeInUp {
            from {
              opacity: 0;
              transform: translateY(30px);
            }
            to {
              opacity: 1;
              transform: translateY(0);
            }
          }

          .stats-grid > div {
            animation: fadeInUp 0.6s ease forwards;
          }

          .stats-grid > div:nth-child(1) { animation-delay: 0.1s; }
          .stats-grid > div:nth-child(2) { animation-delay: 0.2s; }
          .stats-grid > div:nth-child(3) { animation-delay: 0.3s; }
          .stats-grid > div:nth-child(4) { animation-delay: 0.4s; }
          .stats-grid > div:nth-child(5) { animation-delay: 0.5s; }
          .stats-grid > div:nth-child(6) { animation-delay: 0.6s; }
        ` }), s.jsxs("div", { className: "container", style: { padding: "20px", maxWidth: "1400px", margin: "0 auto" }, children: [s.jsxs("div", { className: "page-header", style: { marginBottom: "40px", textAlign: "center", background: "linear-gradient(135deg, #667eea 0%, #764ba2 100%)", padding: "40px 20px", borderRadius: "20px", color: "white", position: "relative", overflow: "hidden" }, children: [s.jsx("div", { style: { position: "absolute", top: "-50%", left: "-50%", width: "200%", height: "200%", background: "radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%)", pointerEvents: "none" } }), s.jsxs("div", { style: { position: "relative", zIndex: 1 }, children: [s.jsx("div", { style: { fontSize: "3rem", marginBottom: "10px" }, children: "🏆" }), s.jsx("h1", { style: { fontSize: "3rem", fontWeight: "bold", color: "white", marginBottom: "15px", textShadow: "0 4px 8px rgba(0,0,0,0.3)" }, children: "Agent Reviews Dashboard" }), s.jsx("p", { style: { fontSize: "1.2rem", color: "rgba(255,255,255,0.9)", marginBottom: "0", maxWidth: "600px", margin: "0 auto" }, children: "Analyze reviewer performance and track app-specific review contributions across all platforms" })] })] }), s.jsxs("div", { className: "two-section-layout", style: { display: "grid", gridTemplateColumns: "300px 1fr", gap: "30px", height: "calc(100vh - 200px)" }, children: [s.jsxs("div", { className: "reviewer-selection-section", style: { background: "rgba(255, 255, 255, 0.95)", backdropFilter: "blur(10px)", borderRadius: "20px", padding: "30px", boxShadow: "0 8px 32px rgba(0,0,0,0.1)", height: "fit-content", border: "1px solid rgba(255,255,255,0.2)" }, children: [s.jsxs("div", { style: { textAlign: "center", marginBottom: "30px" }, children: [s.jsx("div", { style: { fontSize: "2.5rem", marginBottom: "10px" }, children: "👤" }), s.jsx("h3", { style: { fontSize: "1.5rem", fontWeight: "bold", color: "#333", marginBottom: "8px" }, children: "Select Reviewer" }), s.jsx("p", { style: { fontSize: "0.9rem", color: "#666", margin: "0" }, children: "Choose a reviewer to view their app statistics" })] }), _ && s.jsxs("div", { style: { background: "linear-gradient(135deg, #17a2b8 0%, #138496 100%)", padding: "20px", borderRadius: "16px", marginBottom: "25px", color: "white", textAlign: "center", position: "relative", overflow: "hidden" }, children: [s.jsx("div", { style: { position: "absolute", top: "-50%", right: "-50%", width: "200%", height: "200%", background: "radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%)", pointerEvents: "none" } }), s.jsxs("div", { style: { position: "relative", zIndex: 1 }, children: [s.jsx("div", { style: { fontSize: "0.85rem", color: "rgba(255,255,255,0.8)", marginBottom: "8px", textTransform: "uppercase", letterSpacing: "1px" }, children: "Currently Analyzing" }), s.jsx("div", { style: { fontSize: "1.3rem", fontWeight: "bold", color: "white", textShadow: "0 2px 4px rgba(0,0,0,0.3)" }, children: _ }), s.jsx("div", { style: { width: "40px", height: "2px", background: "rgba(255,255,255,0.5)", margin: "10px auto 0", borderRadius: "1px" } })] })] }), s.jsx("div", { className: "reviewer-list", style: { display: "flex", flexDirection: "column", gap: "12px" }, children: m.map((O, V) => {
    const W = _ === O, fe = ["linear-gradient(135deg, #17a2b8 0%, #138496 100%)", "linear-gradient(135deg, #f093fb 0%, #f5576c 100%)", "linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)", "linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)", "linear-gradient(135deg, #fa709a 0%, #fee140 100%)", "linear-gradient(135deg, #a8edea 0%, #fed6e3 100%)"];
    return s.jsxs("button", { className: "custom-selection-button", onClick: () => o(O), tabIndex: -1, style: { width: "100%", padding: "16px 20px", border: "none", borderRadius: "12px", background: W ? fe[V % fe.length] : "rgba(255,255,255,0.98)", color: W ? "white" : "#1a202c", cursor: "pointer", textAlign: "left", fontSize: "1rem", fontWeight: W ? "600" : "500", transition: "all 0.3s ease", position: "relative", overflow: "hidden", boxShadow: W ? "0 8px 25px rgba(0,0,0,0.15)" : "0 2px 8px rgba(0,0,0,0.08)", transform: W ? "translateY(-2px)" : "translateY(0)", outline: "none", WebkitAppearance: "none", MozAppearance: "none", appearance: "none" }, onFocus: (ae) => {
      ae.target.style.outline = "none";
    }, onBlur: (ae) => {
      ae.target.style.outline = "none";
    }, children: [W && s.jsx("div", { style: { position: "absolute", top: "-50%", right: "-50%", width: "200%", height: "200%", background: "radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%)", pointerEvents: "none" } }), s.jsxs("div", { style: { position: "relative", zIndex: 1, display: "flex", alignItems: "center", justifyContent: "space-between" }, children: [s.jsx("span", { children: O }), W && s.jsx("span", { style: { fontSize: "1.2rem" }, children: "✓" })] })] }, O);
  }) }), s.jsx("div", { style: { marginTop: "25px", padding: "15px", background: "rgba(23, 162, 184, 0.1)", borderRadius: "12px", textAlign: "center" }, children: s.jsxs("div", { style: { fontSize: "0.85rem", color: "#666" }, children: ["👥 ", m.length, " reviewers available for analysis"] }) })] }), s.jsxs("div", { className: "reviewer-stats-section", style: { backgroundColor: "white", borderRadius: "8px", padding: "20px", boxShadow: "0 2px 4px rgba(0,0,0,0.1)" }, children: [s.jsxs("h3", { style: { fontSize: "1.3rem", fontWeight: "bold", color: "#333", marginBottom: "20px", borderBottom: "2px solid #17a2b8", paddingBottom: "10px" }, children: ["App Statistics", _ && s.jsxs("span", { style: { fontSize: "1rem", fontWeight: "normal", color: "#666" }, children: [" ", "for ", _, " (All Time)"] })] }), se && s.jsxs("div", { style: { textAlign: "center", padding: "60px 40px", background: "linear-gradient(135deg, #17a2b8 0%, #138496 100%)", borderRadius: "16px", color: "white" }, children: [s.jsx("div", { style: { fontSize: "4rem", marginBottom: "20px", animation: "pulse 2s infinite" }, children: "⏳" }), s.jsx("div", { style: { fontSize: "1.3rem", fontWeight: "500" }, children: "Loading reviewer statistics..." }), s.jsxs("div", { style: { fontSize: "1rem", marginTop: "10px", color: "rgba(255,255,255,0.8)" }, children: ["Analyzing app contributions for ", _] })] }), D && s.jsxs("div", { style: { background: "linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%)", color: "white", padding: "24px", borderRadius: "16px", textAlign: "center", boxShadow: "0 8px 32px rgba(255,107,107,0.3)" }, children: [s.jsx("div", { style: { fontSize: "3rem", marginBottom: "15px" }, children: "⚠️" }), s.jsx("div", { style: { fontSize: "1.2rem", fontWeight: "600", marginBottom: "8px" }, children: "Oops! Something went wrong" }), s.jsx("div", { style: { fontSize: "1rem", color: "rgba(255,255,255,0.9)" }, children: D })] }), !se && !D && c.length === 0 && _ && s.jsxs("div", { style: { textAlign: "center", padding: "60px 40px", background: "linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)", borderRadius: "16px", color: "white" }, children: [s.jsx("div", { style: { fontSize: "4rem", marginBottom: "20px" }, children: "📊" }), s.jsx("div", { style: { fontSize: "1.4rem", fontWeight: "600", marginBottom: "10px" }, children: "No Data Available" }), s.jsxs("div", { style: { fontSize: "1.1rem", color: "rgba(255,255,255,0.9)", maxWidth: "400px", margin: "0 auto" }, children: ["No review data found for ", s.jsx("strong", { children: _ }), ". Try selecting a different reviewer or check back later."] })] }), !se && !D && c.length > 0 && s.jsxs(s.Fragment, { children: [s.jsxs("div", { style: { display: "grid", gridTemplateColumns: "repeat(auto-fit, minmax(200px, 1fr))", gap: "15px", marginBottom: "30px" }, children: [s.jsxs("div", { style: { background: "linear-gradient(135deg, #17a2b8 0%, #138496 100%)", padding: "20px", borderRadius: "12px", color: "white", textAlign: "center" }, children: [s.jsx("div", { style: { fontSize: "2rem", fontWeight: "bold" }, children: c.length }), s.jsx("div", { style: { fontSize: "0.9rem", opacity: 0.9 }, children: "Apps Reviewed" })] }), s.jsxs("div", { style: { background: "linear-gradient(135deg, #f093fb 0%, #f5576c 100%)", padding: "20px", borderRadius: "12px", color: "white", textAlign: "center" }, children: [s.jsx("div", { style: { fontSize: "2rem", fontWeight: "bold" }, children: c.reduce((O, V) => O + V.review_count, 0) }), s.jsx("div", { style: { fontSize: "0.9rem", opacity: 0.9 }, children: "Total Reviews" })] }), s.jsxs("div", { style: { background: "linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)", padding: "20px", borderRadius: "12px", color: "white", textAlign: "center" }, children: [s.jsx("div", { style: { fontSize: "2rem", fontWeight: "bold" }, children: Math.round(c.reduce((O, V) => O + V.review_count, 0) / c.length) }), s.jsx("div", { style: { fontSize: "0.9rem", opacity: 0.9 }, children: "Avg per App" })] })] }), s.jsx("div", { className: "stats-grid", style: { display: "grid", gridTemplateColumns: "repeat(auto-fit, minmax(320px, 1fr))", gap: "20px", marginTop: "20px" }, children: c.sort((O, V) => V.review_count - O.review_count).map((O, V) => {
    const W = V === 0 && O.review_count > 0, fe = V < 3 && O.review_count >= 3;
    return s.jsxs("div", { style: { background: W ? "linear-gradient(135deg, #667eea 0%, #764ba2 100%)" : fe ? "linear-gradient(135deg, #17a2b8 0%, #138496 100%)" : "linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)", borderRadius: "16px", padding: "24px", color: "white", position: "relative", overflow: "hidden", cursor: "pointer", transition: "all 0.3s ease", boxShadow: "0 8px 32px rgba(0,0,0,0.1)", border: "1px solid rgba(255,255,255,0.2)" }, onMouseEnter: (ae) => {
      ae.currentTarget.style.transform = "translateY(-5px)", ae.currentTarget.style.boxShadow = "0 12px 40px rgba(0,0,0,0.2)";
    }, onMouseLeave: (ae) => {
      ae.currentTarget.style.transform = "translateY(0)", ae.currentTarget.style.boxShadow = "0 8px 32px rgba(0,0,0,0.1)";
    }, children: [s.jsx("div", { style: { position: "absolute", top: "-50%", right: "-50%", width: "200%", height: "200%", background: "radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%)", pointerEvents: "none" } }), W && s.jsx("div", { style: { position: "absolute", top: "16px", right: "16px", background: "rgba(255,215,0,0.9)", color: "#333", padding: "4px 8px", borderRadius: "12px", fontSize: "0.75rem", fontWeight: "bold", display: "flex", alignItems: "center", gap: "4px" }, children: "👑 #1" }), fe && !W && s.jsxs("div", { style: { position: "absolute", top: "16px", right: "16px", background: "rgba(255,255,255,0.2)", color: "white", padding: "4px 8px", borderRadius: "12px", fontSize: "0.75rem", fontWeight: "bold" }, children: ["⭐ Top ", V + 1] }), s.jsx("div", { style: { width: "60px", height: "60px", borderRadius: "50%", background: "rgba(255,255,255,0.2)", display: "flex", alignItems: "center", justifyContent: "center", fontSize: "24px", marginBottom: "16px", border: "2px solid rgba(255,255,255,0.3)" }, children: "📱" }), s.jsx("h4", { style: { fontSize: "1.3rem", fontWeight: "600", margin: "0 0 8px 0", color: "white", textShadow: "0 2px 4px rgba(0,0,0,0.3)" }, children: O.app_name }), s.jsxs("div", { style: { display: "flex", alignItems: "center", gap: "8px", marginBottom: "12px" }, children: [s.jsx("div", { style: { fontSize: "2.5rem", fontWeight: "bold", color: "white", textShadow: "0 2px 4px rgba(0,0,0,0.3)" }, children: O.review_count }), s.jsx("div", { style: { fontSize: "1rem", color: "rgba(255,255,255,0.9)", fontWeight: "500" }, children: O.review_count === 1 ? "review" : "reviews" })] }), s.jsxs("div", { style: { display: "flex", alignItems: "center", gap: "8px", fontSize: "0.9rem", color: "rgba(255,255,255,0.8)" }, children: [s.jsx("span", { children: "🏆" }), s.jsx("span", { children: O.review_count >= 10 ? "Major Contributor" : O.review_count >= 5 ? "Active Contributor" : O.review_count >= 2 ? "Regular Contributor" : "New Contributor" })] }), s.jsx("div", { style: { marginTop: "16px", height: "4px", background: "rgba(255,255,255,0.2)", borderRadius: "2px", overflow: "hidden" }, children: s.jsx("div", { style: { height: "100%", background: "rgba(255,255,255,0.8)", borderRadius: "2px", width: `${Math.min(O.review_count / Math.max(...c.map((ae) => ae.review_count)) * 100, 100)}%`, transition: "width 0.3s ease" } }) })] }, V);
  }) })] })] })] })] })] });
};
function Wh() {
  const [m, P] = G.useState("analytics");
  return console.log("App rendering with currentView:", m), G.useEffect(() => {
    const _ = { analytics: "Analytics Dashboard - Shopify App Review Analytics", "access-tabbed": "Access Reviews - Shopify App Review Analytics", "appwise-reviews": "Appwise Reviews - Shopify App Review Analytics", "agent-reviews": "Agent Reviews - Shopify App Review Analytics" };
    document.title = _[m] || "Shopify App Review Analytics";
  }, [m]), s.jsxs("div", { className: "app", children: [s.jsxs("header", { className: "app-header", children: [s.jsx("h1", { children: "Shopify App Review Analytics" }), s.jsx("p", { children: "Comprehensive analytics dashboard for tracking and analyzing Shopify app reviews" }), s.jsxs("div", { className: "nav-tabs", style: { marginTop: "20px" }, children: [s.jsx("button", { className: `nav-tab ${m === "analytics" ? "active" : ""}`, onClick: () => P("analytics"), style: { padding: "10px 20px", marginRight: "10px", border: "none", borderRadius: "5px", backgroundColor: m === "analytics" ? "#007bff" : "#f8f9fa", color: m === "analytics" ? "white" : "#333", cursor: "pointer" }, children: "Analytics" }), s.jsx("button", { className: `nav-tab ${m === "access-tabbed" ? "active" : ""}`, onClick: () => P("access-tabbed"), style: { padding: "10px 20px", marginRight: "10px", border: "none", borderRadius: "5px", backgroundColor: m === "access-tabbed" ? "#007bff" : "#f8f9fa", color: m === "access-tabbed" ? "white" : "#333", cursor: "pointer" }, children: "Access Reviews" }), s.jsx("button", { className: `nav-tab ${m === "appwise-reviews" ? "active" : ""}`, onClick: () => P("appwise-reviews"), style: { padding: "10px 20px", marginRight: "10px", border: "none", borderRadius: "5px", backgroundColor: m === "appwise-reviews" ? "#28a745" : "#f8f9fa", color: m === "appwise-reviews" ? "white" : "#333", cursor: "pointer" }, children: "Appwise Reviews" }), s.jsx("button", { className: `nav-tab ${m === "agent-reviews" ? "active" : ""}`, onClick: () => P("agent-reviews"), style: { padding: "10px 20px", border: "none", borderRadius: "5px", backgroundColor: m === "agent-reviews" ? "#17a2b8" : "#f8f9fa", color: m === "agent-reviews" ? "white" : "#333", cursor: "pointer" }, children: "Agent Reviews" })] })] }), s.jsx("main", { className: "app-main", children: m === "analytics" ? s.jsx(lf, {}) : m === "access-tabbed" ? s.jsx(Ih, {}) : m === "appwise-reviews" ? s.jsx(Uh, {}) : m === "agent-reviews" ? s.jsx(Bh, {}) : s.jsx(lf, {}) })] });
}
Mh.createRoot(document.getElementById("root")).render(s.jsx(G.StrictMode, { children: s.jsx(Wh, {}) }));
