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
import { Injectable } from '@angular/core';
import { MatSnackBar } from '@angular/material';

@Injectable({
  providedIn: 'root'
})
export class SnackbarService {

  private snackbarReference: any;

  constructor(private snackBar: MatSnackBar) { }

  show(message: string, type?: string, duration?: number) {
    this.snackbarReference = this.snackBar.open(message, 'Close', {
      duration: (duration) ? duration : 4000,
      panelClass: [type]
    });

    return this.snackbarReference;
  }

  hide(snackbarReference?: any): void {
    const ref = (snackbarReference) ? snackbarReference : this.snackbarReference;
    ref.dismiss();
  }
}
