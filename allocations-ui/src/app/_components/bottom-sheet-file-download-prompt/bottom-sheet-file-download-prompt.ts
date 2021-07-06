/**
 * Copyrights Allocations 2021. All rights reserved
 *
 * The code, text and other elements of this application/file is copyrighted
 * You may not remove any copyright or other proprietary notices contained in this file
 * The rights granted to you use this application in your organization for your
 * business/personal purpose and not to sell or modify
 *
 * Developed by: Mohamed Asif
 * Email: mohamedasif18@gmail.com
 */
import { Component, Inject } from '@angular/core';
import { MatBottomSheetRef } from '@angular/material/bottom-sheet';
import { MAT_BOTTOM_SHEET_DATA } from '@angular/material';
import { DataShareService } from '../../_services';

@Component({
  templateUrl: 'bottom-sheet-file-download-prompt.html',
})
export class BottomSheetFileDownloadPromptComponent {
  constructor(
    @Inject(MAT_BOTTOM_SHEET_DATA) public data: any,
    private bottomSheetRef: MatBottomSheetRef<BottomSheetFileDownloadPromptComponent>,
    private dataShareService: DataShareService) {
  }

  openLink(action: string, event: MouseEvent, ): void {
    this.dataShareService.sendMessage(action);
    this.bottomSheetRef.dismiss();
    event.preventDefault();
  }
}
