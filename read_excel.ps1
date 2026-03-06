$excel = New-Object -ComObject Excel.Application
$excel.Visible = $false
$excel.DisplayAlerts = $false
$wb = $excel.Workbooks.Open("c:\Users\HP\Downloads\products-export-2026-02-28.xlsx")
$ws = $wb.Sheets.Item(1)
$rows = $ws.UsedRange.Rows.Count
$cols = $ws.UsedRange.Columns.Count
Write-Output "Rows: $rows, Cols: $cols"
Write-Output "=== HEADERS ==="
for ($c = 1; $c -le $cols; $c++) {
    Write-Output "  Col$c = [$($ws.Cells.Item(1,$c).Text)]"
}
for ($r = 2; $r -le [Math]::Min($rows, 6); $r++) {
    Write-Output "=== ROW $r ==="
    for ($c = 1; $c -le $cols; $c++) {
        Write-Output "  $($ws.Cells.Item(1,$c).Text) = [$($ws.Cells.Item($r,$c).Text)]"
    }
}
$wb.Close($false)
$excel.Quit()
[System.Runtime.InteropServices.Marshal]::ReleaseComObject($excel) | Out-Null
