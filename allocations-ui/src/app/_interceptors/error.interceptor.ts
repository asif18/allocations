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
import { HttpRequest, HttpHandler, HttpEvent, HttpInterceptor } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { catchError } from 'rxjs/operators';
import { AuthenticationService, SnackbarService } from '../_services';

@Injectable()
export class ErrorInterceptor implements HttpInterceptor {
  constructor(private authenticationService: AuthenticationService, private snackBar: SnackbarService) {}

  intercept(request: HttpRequest<any>, next: HttpHandler): any {
    return next.handle(request).pipe(catchError((err): any => {
      if (err.status === 401) {
        this.snackBar.show('Session expired. Login to continue', 'danger');
        return this.authenticationService.logout();
      }
      const error = err.error.message || err.statusText;
      this.snackBar.show('Seems invalid action, Restart the application', 'danger');
      return throwError(error);
    }));
  }
}
