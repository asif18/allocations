import { TestBed } from '@angular/core/testing';

import { SendOtpService } from './send-otp.service';

describe('SendOtpService', () => {
  beforeEach(() => TestBed.configureTestingModule({}));

  it('should be created', () => {
    const service: SendOtpService = TestBed.get(SendOtpService);
    expect(service).toBeTruthy();
  });
});
