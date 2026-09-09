function parseURLParams(url) {
  var queryStart = url.indexOf("?") + 1,
    queryEnd = url.indexOf("#") + 1 || url.length + 1,
    query = url.slice(queryStart, queryEnd - 1),
    pairs = query.replace(/\+/g, " ").split("&"),
    parms = {},
    i,
    n,
    v,
    nv;

  if (query === url || query === "") return {};

  for (i = 0; i < pairs.length; i++) {
    nv = pairs[i].split("=", 2);
    n = decodeURIComponent(nv[0]);
    v = decodeURIComponent(nv[1]);

    // eslint-disable-next-line no-prototype-builtins
    if (!parms.hasOwnProperty(n)) parms[n] = [];
    parms[n] = nv.length === 2 ? v : null;
  }
  return parms;
}

// Intercept window.PDFViewerApplicationOptions definition to set options early
let optionsInstance;
Object.defineProperty(window, "PDFViewerApplicationOptions", {
  get() {
    return optionsInstance;
  },
  set(value) {
    optionsInstance = value;
    if (optionsInstance && typeof optionsInstance.setAll === "function") {
      const parseURL = parseURLParams(location.href);
      var annotationModeVal = parseURL.annotationMode !== undefined ? parseInt(parseURL.annotationMode) : 1;
      var externalLinkTargetVal = parseURL.openLinksInNewTab === "1" ? 2 : 4;
      // Progressive (range-request) loading is on by default. Only when explicitly
      // disabled (progressive=0) do we force pdf.js to download the whole file up front.
      var progressiveDisabled = parseURL.progressive === "0";
      optionsInstance.setAll({
        cMapUrl: "cmaps/",
        cMapPacked: true,
        standardFontDataUrl: "standard_fonts/",
        annotationMode: annotationModeVal,
        externalLinkTarget: externalLinkTargetVal,
        disableRange: progressiveDisabled,
        disableStream: progressiveDisabled,
        disableAutoFetch: progressiveDisabled,
      });
    }
  },
  configurable: true,
});

document.addEventListener("DOMContentLoaded", function () {
  const parseURL = parseURLParams(location.href);

  // Keyboard navigation: Left/Right arrows -> previous/next page.
  // Opt-in via the keyboardnav URL param. Kept fully separate from the
  // content-protection keydown handler further below.
  if (parseURL.keyboardnav === "1") {
    document.addEventListener("keydown", function (e) {
      // Don't hijack typing in inputs (page-number box, find bar, forms, etc.)
      const target = e.target;
      const tag = target && target.nodeName ? target.nodeName.toUpperCase() : "";
      if (tag === "INPUT" || tag === "TEXTAREA" || tag === "SELECT" || (target && target.isContentEditable)) {
        return;
      }
      const app = window.PDFViewerApplication;
      if (!app || !app.pdfViewer) return;
      if (e.key === "ArrowLeft" || e.keyCode === 37) {
        app.pdfViewer.previousPage();
        e.preventDefault();
      } else if (e.key === "ArrowRight" || e.keyCode === 39) {
        app.pdfViewer.nextPage();
        e.preventDefault();
      }
    });
  }

  // RTL layout: flip the whole viewer document to right-to-left, which activates
  // pdf.js's own built-in [dir="rtl"] toolbar styling. Applied both now and again
  // after init so pdf.js's locale handling can't override it back to LTR.
  if (parseURL.rtl === "1") {
    document.documentElement.setAttribute("dir", "rtl");
  }

  // Theme: force the viewer chrome light/dark via pdf.js's own built-in
  // .is-light / .is-dark root classes. "auto" adds neither, letting pdf.js
  // follow prefers-color-scheme natively. This themes only the toolbar/chrome,
  // never the PDF page content.
  if (parseURL.theme === "dark") {
    document.documentElement.classList.add("is-dark");
  } else if (parseURL.theme === "light") {
    document.documentElement.classList.add("is-light");
  }

  // Set values on pdfLinkService once it initializes
  const linkServiceInterval = setInterval(() => {
    if (window.PDFViewerApplication && window.PDFViewerApplication.pdfLinkService) {
      clearInterval(linkServiceInterval);
      var externalLinkTargetVal = parseURL.openLinksInNewTab === "1" ? 2 : 4;
      window.PDFViewerApplication.pdfLinkService.externalLinkTarget = externalLinkTargetVal;
      if (parseURL.rtl === "1") {
        document.documentElement.setAttribute("dir", "rtl");
      }
    }
  }, 50);

  // const pdfjsHistory = JSON.parse(window.localStorage.getItem("pdfjs.history"))?.files.find((item) => item.fingerprint === window.PDFViewerApplication?.store?.file?.fingerprint);
  const openFile = document.getElementById("openFile");
  const sidebarToggle = document.getElementById("sidebarToggleButton");
  const print = document.getElementById("printButton");
  const download = document.getElementById("downloadButton");
  const secondaryOpenFile = document.getElementById("secondaryOpenFile");
  const secondaryPrint = document.getElementById("secondaryPrint");
  const secondaryDownload = document.getElementById("secondaryDownload");
  // const viewerContainer = document.getElementById("viewerContainer");
  // const outerContainer = document.getElementById("outerContainer");
  // const toolbar = document.querySelector(".toolbar");
  const presentationMode = document.querySelectorAll(".presentationMode");
  // const pdfViewer = document.querySelector(".pdfViewer");
  // const scrollHorizontalButton = document.getElementById("scrollHorizontal");
  // const scrollVerticalButton = document.getElementById("scrollVertical");
  const documentProperties = document.getElementById("documentPropertiesDialog");
  const editorModeButtons = document.getElementById("editorModeButtons");

  let css = "";
  if (parseURL?.raw) {
    css = `:root{--scrollbar-bg-color:transparent;} body {background:transparent} .toolbar {display: none} .bottombar {display: none} .pdfViewer .page {border-image: url()} #viewerContainer{top:0} `;
    // pdfjsHistory.files[0].sidebarView = 0;
  }
  if (parseURL?.hrscroll) {
    css += ".bottombar{display: none;}";
  }
  const style = document.createElement("style");
  style.innerHTML = css;
  document.querySelector("head").appendChild(style);

  setInterval(() => {
    const canvases = document.querySelectorAll(".canvasWrapper canvas");
    canvases.forEach((canvas) => {
      canvas.toDataURL = () => console.warn("no cheating!");
      canvas.getContext = () => console.warn("no cheating!");
    });
  }, 3000);

  if (sidebarToggle) {
    const shouldOpen = parseURL.open === "true";
    const interval = setInterval(() => {
      if (window.PDFViewerApplication.pdfSidebar.isInitialEventDispatched) {
        if (shouldOpen) {
          window.PDFViewerApplication.pdfSidebar.open();
        } else {
          window.PDFViewerApplication.pdfSidebar.close();
        }
        clearInterval(interval);
      }
    }, 300);
  }

  if (openFile && parseURL?.open) {
    openFile.style.display = "none";
  }

  // rmove print button
  if (parseURL?.stdono != "vera") {
    window.print = () => {
      console.warn("Print disabled!");
    };
    print?.parentNode.removeChild(print);
    secondaryPrint?.parentNode.removeChild(secondaryPrint);
  }

  // remove right sidebar toolbar
  if (parseURL?.isHideRightToolbar === "true" && editorModeButtons) {

    editorModeButtons.parentNode.removeChild(editorModeButtons);
  }


  if (download && parseURL?.nobaki != "vera") {
    window.addEventListener("selectstart", function (e) {
      e.preventDefault();
      console.warn("Content selection disabled!");
    });

    setTimeout(() => {
      documentProperties?.parentNode.removeChild(documentProperties);
    }, 1000);
    download?.parentNode.removeChild(download);
    secondaryDownload?.parentNode.removeChild(secondaryDownload);
  }

  if (secondaryOpenFile && parseURL?.open) {
    secondaryOpenFile.style.display = "none";
  }

  if (presentationMode && parseURL?.fullscreen != "1") {
    Object.values(presentationMode).map((item) => {
      item.style.display = "none";
    });
    // presentationMode.style.display = "none";
  }

  if (location.href.includes("blob:")) {
    download?.parentNode?.removeChild(download);
    secondaryDownload?.parentNode?.removeChild(secondaryDownload);
  }

  //sidebar toggle
  if (sidebarToggle && parseURL?.side != "true") {
    sidebarToggle.style.display = "none";
  }

  //raw css

  const interval = setInterval(() => {
    if (window.PDFViewerApplication.store?.fingerprint) {
      // PDF loaded - clear interval
      clearInterval(interval);

      // change scroll behavior
      setTimeout(() => {
        if (parseURL?.hrscroll === "vera") {
          window.PDFViewerApplication.appConfig.secondaryToolbar.scrollHorizontalButton.click();
        } else {
          window.PDFViewerApplication.appConfig.secondaryToolbar.scrollVerticalButton.click();
        }

        // update zoom level
        if (parseURL.z) {
          window.PDFViewerApplication.pdfViewer.currentScaleValue = parseURL.z ? parseURL.z : "auto";
        }
      }, 100);
    }
  }, 100);

  const disableKey = (e) => {
    if (((e.ctrlKey || e.metaKey) && e.key === "s") || e.key === "F12") {
      e.preventDefault();
      e.stopPropagation();
      alert("Saving is disabled on this page");
      return false;
    } else {
      return true;
    }
  };

  document.addEventListener("keydown", disableKey);
  window.addEventListener("keydown", disableKey);
  document.addEventListener("contextmenu", function (e) {
    e.preventDefault();
  });

  // Listen for PDF.js errors
  const errorInterval = setInterval(() => {
    if (window.PDFViewerApplication && window.PDFViewerApplication.eventBus) {
      clearInterval(errorInterval);

      // Listen for document load errors
      window.PDFViewerApplication.eventBus._on("documenterror", (e) => {
        window.parent.postMessage({
          type: "PDFP_ERROR",
          message: e.message || "An error occurred while loading the PDF."
        }, "*");
      });

      // Listen for other silent failures if possible
      window.PDFViewerApplication.eventBus._on("pagerendererror", (e) => {
        console.error("PDF.js render error:", e);
      });
    }
  }, 500);

  // window.localStorage.setItem('pdfjs.history', JSON.stringify(pdfjsHistory));
});
