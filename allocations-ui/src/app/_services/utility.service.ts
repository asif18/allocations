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

@Injectable({
  providedIn: 'root'
})
export class UtilityService {

  constructor() { }

  downloadBlobFile(body, options, filename) {
    const blob = new Blob([body], options);
    if (navigator.msSaveBlob) { 
      // IE 10+
      navigator.msSaveBlob(blob, filename);
    } 
    else {
      var link = document.createElement('a');
      // Browsers that support HTML5 download attribute
      if (link.download !== undefined)  {
        var url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', filename);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
      }
    }
  }

  getFileNameFromHttpResponse(httpResponse) {
    const contentDispositionHeader = httpResponse.headers.get('Content-Disposition');
    const result = contentDispositionHeader.split(';')[1].trim().split('=')[1];
    return result.replace(/"/g, '');
  }

  base64Encrypt(str: string): string {
    return btoa(str);
  }

  base64Decrypt(str: string): string {
    return atob(str);
  }
}
