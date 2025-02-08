const pdfs = [
  {
    name: "User Guide",
    size: "2.5MB",
    date: "2024-02-08",
    id: "guide",
  },
  {
    name: "Technical Documentation",
    size: "1.8MB",
    date: "2024-02-07",
    id: "tech",
  },
  {
    name: "Report 2024",
    size: "3.2MB",
    date: "2024-02-06",
    id: "report",
  },
];

const pdfList = document.getElementById("pdfList");
const searchInput = document.getElementById("searchInput");

function renderPDFs(pdfs) {
  pdfList.innerHTML = pdfs
    .map(
      (pdf) => `
            <div class="pdf-item">
              <i class="fas fa-file-pdf fa-3x" style="color: #e74c3c;"></i>
              <h3>${pdf.name}</h3>
              <div class="file-info">
                <p><i class="fas fa-weight"></i> Size: ${pdf.size}</p>
                <p><i class="fas fa-calendar"></i> Date: ${pdf.date}</p>
              </div>
              <a href="download.php?file=${pdf.id}" class="download-btn" onclick="return confirmDownload('${pdf.name}')">
                <i class="fas fa-download"></i> Download
              </a>
            </div>
          `
    )
    .join("");
}

searchInput.addEventListener("input", function (e) {
  const searchTerm = e.target.value.toLowerCase();
  const filteredPDFs = pdfs.filter((pdf) =>
    pdf.name.toLowerCase().includes(searchTerm)
  );
  renderPDFs(filteredPDFs);
});

window.confirmDownload = function (filename) {
  return confirm(`Do you want to download ${filename}?`);
};

renderPDFs(pdfs);
